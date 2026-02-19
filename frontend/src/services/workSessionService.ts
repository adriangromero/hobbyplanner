import { mockWorkSessions } from './mockWorkSessions'
import type { WorkSession } from '@/types/WorkSession'

function uuid() {
  return crypto.randomUUID()
}

export const workSessionService = {
  async getByItem(itemId: string): Promise<WorkSession[]> {
    return Promise.resolve(mockWorkSessions.filter(ws => ws.itemId === itemId))
  },

  async add(itemId: string, projectId: string, hours: number, workedAt: Date): Promise<WorkSession> {
    const ws: WorkSession = {
      id: uuid(),
      itemId,
      projectId,
      hours,
      workedAt: workedAt.toISOString()
    }

    mockWorkSessions.push(ws)
    return Promise.resolve(ws)
  }
}
