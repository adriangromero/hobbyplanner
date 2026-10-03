import api from '@/api/axios'
import type { Army, ArmyUnit, PaintingPlan, PaintingSession, UnitComponent } from '@/types/models'

export const armyApi = {
  async list(): Promise<Army[]> {
    const { data } = await api.get('/armies')
    return data.armies
  },

  async create(name: string): Promise<Army> {
    const { data } = await api.post('/armies', { name })
    return data
  },

  async units(armyId: string): Promise<ArmyUnit[]> {
    const { data } = await api.get(`/armies/${armyId}/units`)
    return data.units
  },

  async createUnit(armyId: string, name: string): Promise<ArmyUnit> {
    const { data } = await api.post(`/armies/${armyId}/units`, { name })
    return data
  },

  async addComponent(unitId: string, label: string, quantityTotal: number): Promise<UnitComponent> {
    const { data } = await api.post(`/units/${unitId}/components`, { label, quantityTotal })
    return data
  },

  async updateComponent(component: UnitComponent): Promise<UnitComponent> {
    const { data } = await api.put(`/unit-components/${component.id}`, {
      label: component.label,
      quantityTotal: component.quantityTotal,
    })
    return data
  },

  async adjustPaintedQuantity(componentId: string, delta: number): Promise<UnitComponent> {
    const { data } = await api.put(`/unit-components/${componentId}/progress`, { delta })
    return data
  },

  async removeComponent(componentId: string): Promise<void> {
    await api.delete(`/unit-components/${componentId}`)
  },

  async createPaintingPlan(unitId: string, estimatedHours: number): Promise<PaintingPlan> {
    const { data } = await api.post(`/units/${unitId}/painting-plan`, { estimatedHours })
    return data
  },

  async sessions(planId: string): Promise<PaintingSession[]> {
    const { data } = await api.get(`/painting-plans/${planId}/sessions`)
    return data.sessions
  },

  async recordSession(planId: string, durationSeconds: number): Promise<PaintingSession> {
    const { data } = await api.post(`/painting-plans/${planId}/sessions`, { durationSeconds })
    return data
  },
}
