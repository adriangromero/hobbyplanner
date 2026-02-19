import { mockItems } from './mockItems'
import type { Item } from '@/types/Item'

export const itemService = {
  async getAll(): Promise<Item[]> {
    return Promise.resolve(mockItems)
  },

  async getById(id: string): Promise<Item> {
    const item = mockItems.find(i => i.id === id)
    if (!item) throw new Error('Item not found')
    return Promise.resolve(item)
  },

  async addWorkHours(id: string, hours: number): Promise<Item> {
    const item = mockItems.find(i => i.id === id)
    if (!item) throw new Error('Item not found')
    item.totalHours += hours
    return Promise.resolve(item)
  }
}
