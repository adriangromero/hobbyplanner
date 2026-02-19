import { mockProjects } from './mockProjects'
import type { Project } from '@/types/Project'

export const projectService = {
  async getAll(): Promise<Project[]> {
    return Promise.resolve(mockProjects)
  },

  async getById(id: string): Promise<Project> {
    const project = mockProjects.find(p => p.id === id)
    if (!project) throw new Error('Project not found')
    return Promise.resolve(project)
  }
}
