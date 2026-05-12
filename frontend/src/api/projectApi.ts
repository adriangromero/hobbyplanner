import api from '@/api/axios'
import type { Project, Item, Estimation } from '@/types/models'

interface ProjectDetailResponse {
  project: Project
  items:   Item[]
}

export const projectApi = {
  async list(): Promise<Project[]> {
    const { data } = await api.get('/projects')
    return data.projects
  },

  async detail(id: string): Promise<ProjectDetailResponse> {
    const { data } = await api.get(`/projects/${id}`)
    return data
  },

  async estimation(id: string): Promise<Estimation> {
    const { data } = await api.get(`/projects/${id}/estimation`)
    return data
  },

  async create(name: string, description: string): Promise<Project> {
    const { data } = await api.post('/projects', { name, description })
    return data
  },

  async update(id: string, name: string, description: string): Promise<Project> {
    const { data } = await api.put(`/projects/${id}`, { name, description })
    return data
  },

  async toggleStatus(id: string): Promise<Project> {
    const { data } = await api.put(`/projects/${id}/toggle-status`)
    return data
  },

  async remove(id: string): Promise<void> {
    await api.delete(`/projects/${id}`)
  },
}
