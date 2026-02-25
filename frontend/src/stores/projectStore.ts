import { defineStore } from 'pinia'
import api from '@/api/axios'

type Session = {
  id: string
  startedAt: string
  endedAt: string | null
  durationHours: number
}

type Item = {
  id: string
  name: string
  estimatedHours: number
  totalSessions: number
  sessions: Session[]
}

type Project = {
  id: string
  name: string
  description?: string
  createdAt: string
}

type Estimation = {
  startDate: string | null
  estimatedHours: number
  workedHours: number
  remainingHours: number
  velocityPerDay: number
  daysRemaining: number | null
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

      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al cargar proyecto'
      } finally {
        this.loading = false
      }
    },
  }
})