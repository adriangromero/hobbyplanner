import { defineStore } from 'pinia'
import { useTimerStore } from '@/stores/timerStore'
import { projectApi } from '@/api/projectApi'
import { inventoryApi } from '@/api/inventoryApi'
import { armyApi } from '@/api/armyApi'
import type { Project, ProjectUnit, PaintingPlan, PaintingSession, UnitComponent, Item, Estimation, InventoryItem, Session } from '@/types/models'

export type { Project, Item, InventoryItem }

export const useProjectStore = defineStore('project', {
  state: () => ({
    loading:        false,
    error:          null as string | null,
    projects:       [] as Project[],
    currentProject: null as Project | null,
    items:          [] as Item[],
    estimation:     null as Estimation | null,
    inventory:      [] as InventoryItem[],
    units:          [] as ProjectUnit[],
    sessionsByPlan: {} as Record<string, PaintingSession[]>,
  }),

  actions: {
    async loadProjects() {
      this.loading = true
      this.error   = null

      try {
        this.projects = await projectApi.list()
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar ejércitos'
      } finally {
        this.loading = false
      }
    },

    async loadProject(id: string, sortBy?: string, direction?: 'asc' | 'desc') {
      this.loading = true
      this.error   = null

      if (this.currentProject?.id !== id) {
        this.currentProject = null
        this.items = []
        this.estimation = null
        this.units = []
        this.sessionsByPlan = {}
      }

      try {
        const [detail, estimation] = await Promise.all([
          projectApi.detail(id, sortBy, direction),
          projectApi.estimation(id),
        ])

        this.currentProject = detail.project
        this.items          = detail.items
        this.estimation     = estimation
        this.units          = detail.project.type === 'army' ? await armyApi.units(id) : []
        this.sessionsByPlan = {}

        this.restoreTimer()
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar ejército'
      } finally {
        this.loading = false
      }
    },

    async loadInventory() {
      this.loading = true
      this.error   = null

      try {
        this.inventory = await inventoryApi.list()
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar inventario'
      } finally {
        this.loading = false
      }
    },

    async createUnit(projectId: string, name: string): Promise<ProjectUnit> {
      const unit = await armyApi.createUnit(projectId, name)
      this.units.push(unit)
      this.units.sort((a, b) => a.name.localeCompare(b.name))
      return unit
    },

    async addComponent(unitId: string, label: string, quantityTotal: number): Promise<UnitComponent> {
      const component = await armyApi.addComponent(unitId, label, quantityTotal)
      this.findUnit(unitId)?.components.push(component)
      return component
    },

    async updateComponent(component: UnitComponent): Promise<UnitComponent> {
      const updated = await armyApi.updateComponent(component)
      this.replaceComponent(updated)
      return updated
    },

    async adjustPaintedQuantity(componentId: string, delta: number): Promise<UnitComponent> {
      const component = this.units.flatMap((unit) => unit.components).find((candidate) => candidate.id === componentId)
      const previousQuantity = component?.quantityPainted
      if (component) {
        component.quantityPainted += delta
        component.remainingQuantity = component.quantityTotal - component.quantityPainted
      }
      try {
        const updated = await armyApi.adjustPaintedQuantity(componentId, delta)
        this.replaceComponent(updated)
        return updated
      } catch (error) {
        if (component && previousQuantity !== undefined) {
          component.quantityPainted = previousQuantity
          component.remainingQuantity = component.quantityTotal - previousQuantity
        }
        throw error
      }
    },

    async removeComponent(component: UnitComponent): Promise<void> {
      await armyApi.removeComponent(component.id)
      const unit = this.findUnit(component.unitId)
      if (unit) unit.components = unit.components.filter((candidate) => candidate.id !== component.id)
    },

    async createPaintingPlan(unitId: string, estimatedHours: number): Promise<PaintingPlan> {
      const plan = await armyApi.createPaintingPlan(unitId, estimatedHours)
      const unit = this.findUnit(unitId)
      if (unit) unit.paintingPlan = plan
      this.sessionsByPlan[plan.id] = []
      void this.refreshEstimation()
      return plan
    },

    async loadPaintingSessions(planId: string) {
      this.sessionsByPlan[planId] = await armyApi.sessions(planId)
    },

    async recordPaintingSession(planId: string, durationSeconds: number): Promise<PaintingSession> {
      const session = await armyApi.recordSession(planId, durationSeconds)
      this.applyPaintingSession(session)
      await this.refreshPaintingEstimates()
      return session
    },

    applyPaintingSession(session: PaintingSession) {
      const sessions = this.sessionsByPlan[session.paintingPlanId] ?? []
      this.sessionsByPlan[session.paintingPlanId] = [...sessions, session]
      for (const unit of this.units) {
        const plan = unit.paintingPlan
        if (!plan || plan.id !== session.paintingPlanId) continue
        plan.workedHours += session.durationHours
        plan.remainingHours = Math.max(0, plan.estimatedHours - plan.workedHours)
        break
      }
    },

    async refreshPaintingEstimates() {
      await Promise.all([this.refreshEstimation(), this.refreshArmyUnits()])
    },

    async refreshArmyUnits() {
      if (!this.currentProject || this.currentProject.type !== 'army') return
      try {
        this.units = await armyApi.units(this.currentProject.id)
      } catch {
        // El resumen de unidad se actualizará al volver a cargar el ejército.
      }
    },

    findUnit(unitId: string): ProjectUnit | undefined {
      return this.units.find((unit) => unit.id === unitId)
    },

    replaceComponent(updated: UnitComponent) {
      const unit = this.findUnit(updated.unitId)
      if (!unit) return
      const index = unit.components.findIndex((component) => component.id === updated.id)
      if (index !== -1) unit.components[index] = updated
    },

    restoreTimer() {
      const timer = useTimerStore()

      if (timer.isRunning) return

      for (const item of this.items) {
        if (!item.openSession) continue

        const elapsedSeconds = Math.floor(
          (Date.now() - new Date(item.openSession.startedAt).getTime()) / 1000
        )

        timer.restore(
          item.openSession.id,
          item.id,
          item.name,
          this.currentProject!.id,
          elapsedSeconds,
        )
        break
      }
    },

    async refreshEstimation() {
      if (!this.currentProject) return

      try {
        this.estimation = await projectApi.estimation(this.currentProject.id)
      } catch {
        // silencioso — la estimación es secundaria
      }
    },

    addSessionToItem(itemId: string, session: Session) {
      const item = this.items.find(i => i.id === itemId)
      if (!item) return

      item.openSession = null
      item.totalSessions++
      if (session.endedAt !== null) {
        item.totalHours += session.durationHours
      }

      this.refreshEstimation()
    },

    adjustItemTotalHours(itemId: string, hoursDelta: number, sessionCountDelta = 0) {
      const item = this.items.find(i => i.id === itemId)
      if (!item) return

      item.totalHours    = Math.max(0, item.totalHours + hoursDelta)
      item.totalSessions = Math.max(0, item.totalSessions + sessionCountDelta)

      this.refreshEstimation()
    },

    addItem(item: Item) {
      this.items.push(item)
      this.refreshEstimation()
    },

    addProject(project: Project) {
      this.projects.push(project)
    },

    updateProject(updated: { id: string; name: string; description: string; status?: string }) {
      const project = this.projects.find((p: Project) => p.id === updated.id)
      if (!project) return

      project.name        = updated.name
      project.description = updated.description
      if (updated.status) project.status = updated.status as Project['status']
    },

    removeProject(projectId: string) {
      this.projects = this.projects.filter((p: Project) => p.id !== projectId)
    },

    updateItem(updated: { id: string; name: string; estimatedHours: number; status?: string }) {
      const item = this.items.find(i => i.id === updated.id)
      if (!item) return

      item.name           = updated.name
      item.estimatedHours = updated.estimatedHours
      if (updated.status) item.status = updated.status as Item['status']
      this.refreshEstimation()
    },

    toggleItemStatus(itemId: string, newStatus: string) {
      const item = this.items.find(i => i.id === itemId)
      if (!item) return

      item.status = newStatus as Item['status']
      this.refreshEstimation()
    },

    toggleProjectStatus(newStatus: string) {
      if (!this.currentProject) return
      this.currentProject.status = newStatus as Project['status']

      const project = this.projects.find(p => p.id === this.currentProject!.id)
      if (project) project.status = newStatus as Project['status']
    },

    removeItem(itemId: string) {
      this.items = this.items.filter(i => i.id !== itemId)
      this.refreshEstimation()
    },
  }
})
