import api from '@/api/axios'
import type { Session } from '@/types/models'

interface StartSessionResponse {
  id:        string
  itemId:    string
  projectId: string
  startedAt: string
}

export const sessionApi = {
  async start(itemId: string, projectId: string): Promise<StartSessionResponse> {
    const { data } = await api.post('/work-sessions', { itemId, projectId })
    return data
  },

  async finish(id: string): Promise<Session> {
    const { data } = await api.put(`/work-sessions/${id}/finish`)
    return data
  },

  async update(id: string, startedAt: string, endedAt: string | null): Promise<Session> {
    const { data } = await api.put(`/work-sessions/${id}`, { startedAt, endedAt })
    return data
  },

  async remove(id: string): Promise<void> {
    await api.delete(`/work-sessions/${id}`)
  },
}
