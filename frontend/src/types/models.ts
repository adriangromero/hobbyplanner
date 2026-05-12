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
  createdAt:    string
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

export type InventoryItem = Item & {
  projectId:   string
  projectName: string
}
