import type { Item } from '@/types/Item'

export const mockItems: Item[] = [
  // Forced estimate examples
  {
    id: 'i1',
    projectId: 'p1',
    name: 'Knight 1',
    description: 'Full armor, shield, and horse.',
    estimatedHours: 1.5, // forced
    totalHours: 3,
    status: 'completed'
  },
  {
    id: 'i2',
    projectId: 'p1',
    name: 'Knight 2',
    description: 'Full armor, shield, and horse.',
    estimatedHours: 1.5, // forced
    totalHours: 0,
    status: 'not_started'
  },

  // Auto estimate examples (estimatedHours = 0)
  {
    id: 'i3',
    projectId: 'p1',
    name: 'Knight 3',
    description: 'Auto-estimated knight.',
    estimatedHours: 0,
    totalHours: 0,
    status: 'not_started'
  },
  {
    id: 'i4',
    projectId: 'p1',
    name: 'Knight 4',
    description: 'Auto-estimated knight.',
    estimatedHours: 0,
    totalHours: 0,
    status: 'not_started'
  }

  // You can add up to 40 items following this pattern
]
