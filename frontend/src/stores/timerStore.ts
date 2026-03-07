import { defineStore } from 'pinia'
import api from '@/api/axios'

interface SessionData {
  id:            string
  itemId:        string
  startedAt:     string
  endedAt:       string
  durationHours: number
}

interface TimerState {
  isRunning:       boolean
  sessionId:       string | null
  activeItemId:    string | null
  activeItemName:  string | null
  activeProjectId: string | null
  elapsedSeconds:  number
  interval:        ReturnType<typeof setInterval> | null
}

export const useTimerStore = defineStore('timer', {
  state: (): TimerState => ({
    isRunning:       false,
    sessionId:       null,
    activeItemId:    null,
    activeItemName:  null,
    activeProjectId: null,
    elapsedSeconds:  0,
    interval:        null,
  }),

  getters: {
    elapsedFormatted: (state): string => {
      const h  = Math.floor(state.elapsedSeconds / 3600)
      const m  = Math.floor((state.elapsedSeconds % 3600) / 60)
      const s  = state.elapsedSeconds % 60
      const mm = String(m).padStart(2, '0')
      const ss = String(s).padStart(2, '0')
      return h > 0 ? `${h}:${mm}:${ss}` : `${mm}:${ss}`
    }
  },

  actions: {
    async start(itemId: string, itemName: string, projectId: string) {
      if (this.isRunning) {
        throw new Error(`Ya tienes una sesión activa en "${this.activeItemName}". Finalízala antes de iniciar otra.`)
      }

      try {
        const { data } = await api.post('/work-sessions', { itemId, projectId })

        this.sessionId       = data.id
        this.activeItemId    = itemId
        this.activeItemName  = itemName
        this.activeProjectId = projectId
        this.elapsedSeconds  = 0
        this.isRunning       = true

        this.interval = setInterval(() => {
          this.elapsedSeconds++
        }, 1000)

      } catch (e) {
        console.error('Error al iniciar sesión', e)
        throw e
      }
    },

    // Restaurar timer desde sesión abierta en BD
    restore(
      sessionId: string,
      itemId: string,
      itemName: string,
      projectId: string,
      elapsedSeconds: number,
    ) {
      this.sessionId       = sessionId
      this.activeItemId    = itemId
      this.activeItemName  = itemName
      this.activeProjectId = projectId
      this.elapsedSeconds  = elapsedSeconds
      this.isRunning       = true

      this.interval = setInterval(() => {
        this.elapsedSeconds++
      }, 1000)
    },

    async stop(): Promise<SessionData | null> {
      if (!this.sessionId) return null

      // Para el intervalo INMEDIATAMENTE — fix del delay visual
      if (this.interval) {
        clearInterval(this.interval)
        this.interval = null
      }
      this.isRunning = false

      const sessionId = this.sessionId

      try {
        const { data } = await api.put(`/work-sessions/${sessionId}/finish`)
        return data
      } catch (e) {
        console.error('Error al finalizar sesión', e)
        return null
      } finally {
        this.reset()
      }
    },

    reset() {
      if (this.interval) clearInterval(this.interval)

      this.isRunning       = false
      this.sessionId       = null
      this.activeItemId    = null
      this.activeItemName  = null
      this.activeProjectId = null
      this.elapsedSeconds  = 0
      this.interval        = null
    }
  }
})