import { defineStore } from 'pinia'
import api from '@/api/axios'
import { useTimerStore } from '@/stores/timerStore'

type Session = {
  id:            string
  startedAt:     string
  endedAt:       string | null
  durationHours: number
}

type OpenSession = {
  id:        string
  startedAt: string
}

type Item = {
  id:             string
  name:           string
  estimatedHours: number
  totalSessions:  number
  totalHours:     number
  openSession:    OpenSession | null
}

type Project = {
  id:           string
  name:         string
  description?: string
  createdAt:    string
}

type Estimation = {
  startDate:               string | null
  estimatedHours:          number
  workedHours:             number
  remainingHours:          number
  velocityPerActiveDay:    number
  activeDays:              number
  frequencyDaysPerWeek:    number
  activeDaysRemaining:     number | null
  daysRemaining:           number | null
  estimatedCompletionDate: string | null
}

export const useProjectStore = defineStore('project', {
  state: () => ({
    loading:        false,
    error:          null as string | null,
    projects:       [] as Project[],
    currentProject: null as Project | null,
    items:          [] as Item[],
    estimation:     null as Estimation | null,
  }),

  actions: {
    async loadProjects() {
      this.loading = true
      this.error   = null

      try {
        const { data } = await api.get('/projects')
        this.projects = data.projects
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar proyectos'
      } finally {
        this.loading = false
      }
    },

    async loadProject(id: string) {
      this.loading = true
      this.error   = null

      try {
        const [projectRes, estimationRes] = await Promise.all([
          api.get(`/projects/${id}`),
          api.get(`/projects/${id}/estimation`),
        ])

        this.currentProject = projectRes.data.project
        this.items          = projectRes.data.items
        this.estimation     = estimationRes.data

        // Restaurar timer si hay sesión abierta
        this.restoreTimer()

      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar proyecto'
      } finally {
        this.loading = false
      }
    },

    restoreTimer() {
      const timer = useTimerStore()

      // Si ya hay un timer corriendo no hacemos nada
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
        const { data } = await api.get(`/projects/${this.currentProject.id}/estimation`)
        this.estimation = data
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

    updateProject(updated: { id: string; name: string; description: string }) {
      const project = this.projects.find((p: Project) => p.id === updated.id)
      if (!project) return

      project.name        = updated.name
      project.description = updated.description
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

    removeItem(itemId: string) {
      this.items = this.items.filter(i => i.id !== itemId)
      this.refreshEstimation()
    },
  }
})