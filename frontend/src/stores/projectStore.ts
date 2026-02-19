import { defineStore } from 'pinia'

type Item = {
  id: string
  name: string
  estimatedHours: number
  workedHours: number
}

type Session = {
  id: string
  itemId: string
  projectId: string
  duration: number
  createdAt: string
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
    loading: false,
    projects: [] as Project[],
    currentProject: null as Project | null,
    items: [] as Item[],
    sessions: [] as Session[],
    estimation: null as Estimation | null
  }),

  actions: {
    async loadProjects() {
      this.loading = true
      const res = await fetch('http://localhost/projects')
      const data = await res.json()
      this.projects = data.projects
      this.loading = false
    },

    async loadProject(id: string) {
      this.loading = true
      const res = await fetch(`http://localhost/projects/${id}`)
      const data = await res.json()

      this.currentProject = data.project
      this.items = data.items
      this.sessions = data.sessions

      // cargar estimación en paralelo
      const estRes = await fetch(`http://localhost/projects/${id}/estimation`)
      this.estimation = await estRes.json()

      this.loading = false
    },

    addSession(payload: { itemId: string; projectId: string; duration: number }) {
      this.sessions.push({
        id: crypto.randomUUID(),
        itemId: payload.itemId,
        projectId: payload.projectId,
        duration: payload.duration,
        createdAt: new Date().toISOString()
      })

      const item = this.items.find(i => i.id === payload.itemId)
      if (item) {
        item.workedHours += payload.duration / 3600
      }
    }
  }
})
