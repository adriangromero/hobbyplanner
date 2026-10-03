import { defineStore } from 'pinia'
import { armyApi } from '@/api/armyApi'
import type { Army, ArmyUnit, PaintingPlan, PaintingSession, UnitComponent } from '@/types/models'

export const useArmyStore = defineStore('army', {
  state: () => ({
    loading: false,
    error: null as string | null,
    armies: [] as Army[],
    currentArmy: null as Army | null,
    units: [] as ArmyUnit[],
    sessionsByPlan: {} as Record<string, PaintingSession[]>,
  }),

  actions: {
    async loadArmies() {
      this.loading = true
      this.error = null
      try {
        this.armies = await armyApi.list()
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar los ejércitos'
      } finally {
        this.loading = false
      }
    },

    async createArmy(name: string): Promise<Army> {
      const army = await armyApi.create(name)
      this.armies.push(army)
      return army
    },

    async loadArmyDetail(armyId: string) {
      this.loading = true
      this.error = null
      this.currentArmy = null
      this.units = []
      try {
        const [armies, units] = await Promise.all([
          armyApi.list(),
          armyApi.units(armyId),
        ])
        this.armies = armies
        this.currentArmy = armies.find((army) => army.id === armyId) ?? null
        this.units = units
        this.sessionsByPlan = {}
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar el ejército'
      } finally {
        this.loading = false
      }
    },

    async createUnit(armyId: string, name: string): Promise<ArmyUnit> {
      const unit = await armyApi.createUnit(armyId, name)
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
      return plan
    },

    async loadPaintingSessions(planId: string) {
      this.sessionsByPlan[planId] = await armyApi.sessions(planId)
    },

    async recordPaintingSession(planId: string, durationSeconds: number): Promise<PaintingSession> {
      const session = await armyApi.recordSession(planId, durationSeconds)
      this.applyPaintingSession(session)
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

    async refreshCurrentArmy() {
      if (this.currentArmy) await this.loadArmyDetail(this.currentArmy.id)
    },

    findUnit(unitId: string): ArmyUnit | undefined {
      return this.units.find((unit) => unit.id === unitId)
    },

    replaceComponent(updated: UnitComponent) {
      const unit = this.findUnit(updated.unitId)
      if (!unit) return
      const index = unit.components.findIndex((component) => component.id === updated.id)
      if (index !== -1) unit.components[index] = updated
    },
  },
})
