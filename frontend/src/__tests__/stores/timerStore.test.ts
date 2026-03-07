import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useTimerStore } from '@/stores/timerStore'

describe('timerStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  it('starts with default state', () => {
    const timer = useTimerStore()

    expect(timer.isRunning).toBe(false)
    expect(timer.sessionId).toBeNull()
    expect(timer.elapsedSeconds).toBe(0)
  })

  it('elapsedFormatted shows mm:ss when under 1 hour', () => {
    const timer = useTimerStore()
    timer.elapsedSeconds = 125 // 2:05

    expect(timer.elapsedFormatted).toBe('02:05')
  })

  it('elapsedFormatted shows h:mm:ss when over 1 hour', () => {
    const timer = useTimerStore()
    timer.elapsedSeconds = 3661 // 1:01:01

    expect(timer.elapsedFormatted).toBe('1:01:01')
  })

  it('restore sets state and starts interval', () => {
    const timer = useTimerStore()
    timer.restore('session-1', 'item-1', 'Item Name', 'project-1', 100)

    expect(timer.isRunning).toBe(true)
    expect(timer.sessionId).toBe('session-1')
    expect(timer.activeItemId).toBe('item-1')
    expect(timer.activeItemName).toBe('Item Name')
    expect(timer.activeProjectId).toBe('project-1')
    expect(timer.elapsedSeconds).toBe(100)

    vi.advanceTimersByTime(3000)
    expect(timer.elapsedSeconds).toBe(103)
  })

  it('reset clears all state', () => {
    const timer = useTimerStore()
    timer.restore('session-1', 'item-1', 'Item Name', 'project-1', 50)

    timer.reset()

    expect(timer.isRunning).toBe(false)
    expect(timer.sessionId).toBeNull()
    expect(timer.activeItemId).toBeNull()
    expect(timer.elapsedSeconds).toBe(0)
    expect(timer.interval).toBeNull()
  })
})
