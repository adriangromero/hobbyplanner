import api from '@/api/axios'
import type { Item, Session } from '@/types/models'

export const itemApi = {
  async create(projectId: string, name: string, estimatedHours: number): Promise<Item> {
    const { data } = await api.post('/items', { projectId, name, estimatedHours })
    return data
  },

  async update(id: string, name: string, estimatedHours: number): Promise<Item> {
    const { data } = await api.put(`/items/${id}`, { name, estimatedHours })
    return data
  },

  async toggleStatus(id: string): Promise<{ id: string; status: string }> {
    const { data } = await api.put(`/items/${id}/toggle-status`)
    return data
  },

  async remove(id: string): Promise<void> {
    await api.delete(`/items/${id}`)
  },

  async sessions(id: string): Promise<Session[]> {
    const { data } = await api.get(`/items/${id}/sessions`)
    return data.sessions
  },
}
