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

export const useProjectStore = defineStore('project', {
  state: () => ({
    loading: false,

    // LISTA DE PROYECTOS
    projects: [] as Project[],

    // DETALLE DE UN PROYECTO
    currentProject: null as Project | null,
    items: [] as Item[],
    sessions: [] as Session[]
  }),

  actions: {
    // ⭐ Cargar TODOS los proyectos
    async loadProjects() {
      this.loading = true

      const res = await fetch('http://localhost/projects')
      const data = await res.json()

      // Backend devuelve { projects: [...] }
      this.projects = data.projects

      this.loading = false
    },

    // ⭐ Cargar un proyecto completo (project + items + sessions)
    async loadProject(id: string) {
      this.loading = true

      const res = await fetch(`http://localhost/projects/${id}`)
      const data = await res.json()

      this.currentProject = data.project
      this.items = data.items
      this.sessions = data.sessions

      this.loading = false
    },

    // ⭐ Añadir sesión (actualiza workedHours del item)
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
