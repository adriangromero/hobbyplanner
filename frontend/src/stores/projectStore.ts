import { defineStore } from 'pinia'
import { useTimerStore } from '@/stores/timerStore'
import { projectApi } from '@/api/projectApi'
import { inventoryApi } from '@/api/inventoryApi'
import type { Project, Item, Estimation, InventoryItem, Session } from '@/types/models'

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
  }),

  actions: {
    async loadProjects() {
      this.loading = true
      this.error   = null

      try {
        this.projects = await projectApi.list()
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar proyectos'
      } finally {
        this.loading = false
      }
    },

    async loadProject(id: string, sortBy?: string, direction?: 'asc' | 'desc') {
      this.loading = true
      this.error   = null

      try {
        const [detail, estimation] = await Promise.all([
          projectApi.detail(id, sortBy, direction),
          projectApi.estimation(id),
        ])

        this.currentProject = detail.project
        this.items          = detail.items
        this.estimation     = estimation

        this.restoreTimer()
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar proyecto'
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

    updateItem(updated: { id: string; name: string; estimatedHours: number }) {
      const item = this.items.find(i => i.id === updated.id)
      if (!item) return

      item.name           = updated.name
      item.estimatedHours = updated.estimatedHours
      this.refreshEstimation()
    },

    updateItemStatus(itemId: string, status: string) {
      const item = this.items.find(i => i.id === itemId)
      if (!item) return

      item.status = status as Item['status']
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
