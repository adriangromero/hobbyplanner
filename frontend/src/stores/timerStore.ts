import { defineStore } from 'pinia'
import { sessionApi } from '@/api/sessionApi'
import { armyApi } from '@/api/armyApi'
import type { PaintingSession, Session } from '@/types/models'

const PAINTING_TIMER_KEY = 'active_painting_timer'

export type TimerStopResult =
  | { kind: 'work-session'; itemId: string; session: Session }
  | { kind: 'painting-plan'; planId: string; session: PaintingSession }

interface TimerState {
  isRunning:       boolean
  sessionId:       string | null
  activeItemId:    string | null
  activeItemName:  string | null
  activeProjectId: string | null
  activePaintingPlanId: string | null
  activeTargetType: 'work-session' | 'painting-plan' | null
  startedAtEpochMs: number | null
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
    activePaintingPlanId: null,
    activeTargetType: null,
    startedAtEpochMs: null,
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

      const data = await sessionApi.start(itemId, projectId)

      this.sessionId       = data.id
      this.activeItemId    = itemId
      this.activeItemName  = itemName
      this.activeProjectId = projectId
      this.activePaintingPlanId = null
      this.activeTargetType = 'work-session'
      this.startedAtEpochMs = Date.now()
      this.elapsedSeconds  = 0
      this.isRunning       = true

      this.startTicker()
    },

    startPaintingPlan(planId: string, planName: string) {
      if (this.isRunning) {
        throw new Error(`Ya tienes una sesión activa en "${this.activeItemName}". Finalízala antes de iniciar otra.`)
      }

      const startedAt = Date.now()
      this.sessionId = null
      this.activeItemId = null
      this.activeItemName = planName
      this.activeProjectId = null
      this.activePaintingPlanId = planId
      this.activeTargetType = 'painting-plan'
      this.startedAtEpochMs = startedAt
      this.elapsedSeconds = 0
      this.isRunning = true
      localStorage.setItem(PAINTING_TIMER_KEY, JSON.stringify({ planId, planName, startedAt }))
      this.startTicker()
    },

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
      this.activePaintingPlanId = null
      this.activeTargetType = 'work-session'
      this.startedAtEpochMs = Date.now() - elapsedSeconds * 1000
      this.elapsedSeconds  = elapsedSeconds
      this.isRunning       = true

      this.startTicker()
    },

    restorePaintingTimerFromStorage() {
      if (this.isRunning) return
      try {
        const saved = localStorage.getItem(PAINTING_TIMER_KEY)
        if (!saved) return
        const timer = JSON.parse(saved) as { planId?: string; planName?: string; startedAt?: number }
        if (!timer.planId || !timer.planName || typeof timer.startedAt !== 'number') {
          localStorage.removeItem(PAINTING_TIMER_KEY)
          return
        }
        this.sessionId = null
        this.activeItemId = null
        this.activeItemName = timer.planName
        this.activeProjectId = null
        this.activePaintingPlanId = timer.planId
        this.activeTargetType = 'painting-plan'
        this.startedAtEpochMs = timer.startedAt
        this.elapsedSeconds = Math.max(0, Math.floor((Date.now() - timer.startedAt) / 1000))
        this.isRunning = true
        this.startTicker()
      } catch {
        localStorage.removeItem(PAINTING_TIMER_KEY)
      }
    },

    async stop(): Promise<TimerStopResult | null> {
      if (!this.isRunning) return null
      const targetType = this.activeTargetType
      const itemId = this.activeItemId
      const planId = this.activePaintingPlanId
      const sessionId = this.sessionId
      const durationSeconds = Math.max(1, this.elapsedSeconds)
      this.clearTicker()
      this.isRunning = false

      try {
        if (targetType === 'painting-plan' && planId) {
          const session = await armyApi.recordSession(planId, durationSeconds)
          return { kind: 'painting-plan', planId, session }
        }
        if (targetType === 'work-session' && sessionId && itemId) {
          const session = await sessionApi.finish(sessionId)
          return { kind: 'work-session', itemId, session }
        }
        return null
      } catch {
        return null
      } finally {
        this.reset()
      }
    },

    startTicker() {
      this.clearTicker()
      this.interval = setInterval(() => {
        if (this.startedAtEpochMs !== null) {
          this.elapsedSeconds = Math.max(0, Math.floor((Date.now() - this.startedAtEpochMs) / 1000))
        }
      }, 1000)
    },

    clearTicker() {
      if (this.interval) clearInterval(this.interval)
      this.interval = null
    },

    reset() {
      this.clearTicker()
      localStorage.removeItem(PAINTING_TIMER_KEY)

      this.isRunning       = false
      this.sessionId       = null
      this.activeItemId    = null
      this.activeItemName  = null
      this.activeProjectId = null
      this.activePaintingPlanId = null
      this.activeTargetType = null
      this.startedAtEpochMs = null
      this.elapsedSeconds  = 0
      this.interval        = null
    }
  }
})
