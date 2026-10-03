import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useProjectStore } from '@/stores/projectStore'

describe('projectStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('addItem pushes item to list', () => {
    const store = useProjectStore()
    store.currentProject = { id: 'p1', name: 'Project', status: 'active', createdAt: '2026-01-01' }

    const item = {
      id: 'i1',
      name: 'Item 1',
      estimatedHours: 5,
      status: 'pending' as const,
      createdAt: '2026-01-01',
      totalSessions: 0,
      totalHours: 0,
      openSession: null,
    }

    store.addItem(item)

    expect(store.items).toHaveLength(1)
    expect(store.items[0].name).toBe('Item 1')
  })

  it('removeItem filters item from list', () => {
    const store = useProjectStore()
    store.items = [
      { id: 'i1', name: 'A', estimatedHours: 5, status: 'pending', createdAt: '2026-01-01', totalSessions: 0, totalHours: 0, openSession: null },
      { id: 'i2', name: 'B', estimatedHours: 3, status: 'pending', createdAt: '2026-01-01', totalSessions: 0, totalHours: 0, openSession: null },
    ]

    store.removeItem('i1')

    expect(store.items).toHaveLength(1)
    expect(store.items[0].id).toBe('i2')
  })

  it('updateItem modifies item in place', () => {
    const store = useProjectStore()
    store.items = [
      { id: 'i1', name: 'Old', estimatedHours: 5, status: 'pending', createdAt: '2026-01-01', totalSessions: 0, totalHours: 0, openSession: null },
    ]

    store.updateItem({ id: 'i1', name: 'New', estimatedHours: 10 })

    expect(store.items[0].name).toBe('New')
    expect(store.items[0].estimatedHours).toBe(10)
  })

  it('addSessionToItem updates counters and clears openSession', () => {
    const store = useProjectStore()
    store.items = [
      {
        id: 'i1', name: 'Item', estimatedHours: 5, status: 'in_progress', createdAt: '2026-01-01',
        totalSessions: 1, totalHours: 2,
        openSession: { id: 's1', startedAt: '2026-01-01T10:00:00' },
      },
    ]

    store.addSessionToItem('i1', {
      id: 's1',
      startedAt: '2026-01-01T10:00:00',
      endedAt: '2026-01-01T11:30:00',
      durationHours: 1.5,
    })

    expect(store.items[0].openSession).toBeNull()
    expect(store.items[0].totalSessions).toBe(2)
    expect(store.items[0].totalHours).toBe(3.5)
  })

  it('adjustItemTotalHours adjusts hours and session count', () => {
    const store = useProjectStore()
    store.items = [
      { id: 'i1', name: 'Item', estimatedHours: 5, status: 'pending', createdAt: '2026-01-01', totalSessions: 3, totalHours: 5, openSession: null },
    ]

    store.adjustItemTotalHours('i1', -1.5, -1)

    expect(store.items[0].totalHours).toBe(3.5)
    expect(store.items[0].totalSessions).toBe(2)
  })

  it('addProject pushes project to list', () => {
    const store = useProjectStore()
    store.addProject({ id: 'p1', name: 'New Project', status: 'active', createdAt: '2026-01-01' })

    expect(store.projects).toHaveLength(1)
  })

  it('removeProject filters project from list', () => {
    const store = useProjectStore()
    store.projects = [
      { id: 'p1', name: 'A', status: 'active', createdAt: '2026-01-01' },
      { id: 'p2', name: 'B', status: 'active', createdAt: '2026-01-02' },
    ]

    store.removeProject('p1')

    expect(store.projects).toHaveLength(1)
    expect(store.projects[0].id).toBe('p2')
  })
})
