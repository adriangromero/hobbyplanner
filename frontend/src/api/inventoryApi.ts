import api from '@/api/axios'
import type { InventoryItem } from '@/types/models'

export const inventoryApi = {
  async list(): Promise<InventoryItem[]> {
    const { data } = await api.get('/inventory')
    return data.items
  },
}
