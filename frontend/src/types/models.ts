export type OpenSession = {
  id:        string
  startedAt: string
}

export type Session = {
  id:            string
  startedAt:     string
  endedAt:       string | null
  durationHours: number
}

export type Item = {
  id:             string
  name:           string
  estimatedHours: number
  status:         'pending' | 'in_progress' | 'completed'
  createdAt:      string
  totalSessions:  number
  totalHours:     number
  openSession:    OpenSession | null
}

export type Project = {
  id:           string
  name:         string
  description?: string
  status:       'active' | 'completed'
  type:         'general' | 'army'
  createdAt:    string
  unitCount:    number
  miniatureCount: number
}

export type Estimation = {
  startDate:               string | null
  estimatedHours:          number
  workedHours:             number
  remainingHours:          number
  velocityPerActiveDay:    number
  activeDays:              number
  frequencyDaysPerWeek:    number
  activeDaysRemaining:     number | null
  daysRemaining:           number | null
  estimatedCompletionDate: string | null
}

export const UNIT_CATEGORIES = [
  { value: 'character', label: 'Personaje' },
  { value: 'hero', label: 'Héroe' },
  { value: 'infantry', label: 'Infantería' },
  { value: 'special', label: 'Especial' },
  { value: 'singular', label: 'Singular' },
  { value: 'war_machine', label: 'Máquina de guerra' },
] as const

export type UnitCategory = typeof UNIT_CATEGORIES[number]['value']

export type InventoryItem = Item & {
  projectId:   string
  projectName: string
}

export type PaintingPlan = {
  id: string
  unitId: string
  estimatedHours: number
  workedHours: number
  remainingHours: number
  sessionCount: number
  estimation?: Estimation | null
}

export type UnitComponent = {
  id: string
  unitId: string
  label: string
  quantityTotal: number
  quantityPainted: number
  remainingQuantity: number
}

export type ProjectUnit = {
  id: string
  projectId: string
  name: string
  category: UnitCategory
  position: number
  modelsPerRow: number
  components: UnitComponent[]
  paintingPlan: PaintingPlan | null
}

export type PaintingSession = {
  id: string
  paintingPlanId: string
  durationSeconds: number
  durationHours: number
  workedAt: string
}
