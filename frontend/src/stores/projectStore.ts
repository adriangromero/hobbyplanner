import { defineStore } from 'pinia'
import api from '@/api/axios'
import { useTimerStore } from '@/stores/timerStore'

type Session = {
  id:            string
  startedAt:     string
  endedAt:       string | null
  durationHours: number
}

type Item = {
  id:             string
  name:           string
  estimatedHours: number
  totalSessions:  number
  sessions:       Session[]
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
  velocityPerDay:          number
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
        const openSession = item.sessions.find(s => s.endedAt === null)

        if (openSession) {
          // Calcular segundos transcurridos desde que empezó
          const elapsedSeconds = Math.floor(
            (Date.now() - new Date(openSession.startedAt).getTime()) / 1000
          )

          timer.restore(
            openSession.id,
            item.id,
            item.name,
            this.currentProject!.id,
            elapsedSeconds,
          )
          break
        }
      }
    },

    addSessionToItem(itemId: string, session: Session) {
      const item = this.items.find(i => i.id === itemId)
      if (!item) return

      item.totalSessions++
      item.sessions.unshift(session)
    },

    updateSession(itemId: string, updatedSession: Session) {
      const item = this.items.find(i => i.id === itemId)
      if (!item) return

      const index = item.sessions.findIndex(s => s.id === updatedSession.id)
      if (index !== -1) {
        item.sessions[index] = updatedSession
      }
    },

    removeSessionFromItem(itemId: string, sessionId: string) {
      const item = this.items.find(i => i.id === itemId)
      if (!item) return

      item.sessions      = item.sessions.filter(s => s.id !== sessionId)
      item.totalSessions = Math.max(0, item.totalSessions - 1)
    },
  }
})