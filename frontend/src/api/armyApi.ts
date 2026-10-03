import api from '@/api/axios'
import type { ProjectUnit, PaintingPlan, PaintingSession, UnitComponent } from '@/types/models'

export const armyApi = {
  async units(projectId: string): Promise<ProjectUnit[]> {
    const { data } = await api.get(`/projects/${projectId}/units`)
    return data.units
  },

  async createUnit(projectId: string, name: string): Promise<ProjectUnit> {
    const { data } = await api.post(`/projects/${projectId}/units`, { name })
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
