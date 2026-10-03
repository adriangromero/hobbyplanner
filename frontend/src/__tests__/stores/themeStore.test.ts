import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useThemeStore } from '@/stores/themeStore'

describe('themeStore', () => {
  beforeEach(() => {
    localStorage.clear()
    document.documentElement.dataset.theme = 'cozy'
    setActivePinia(createPinia())
  })

  it('keeps a visual theme per army and restores it when selected again', () => {
    const store = useThemeStore()

    store.activateArmy('army-1')
    store.setArmyTheme('army-1', 'imperial')
    expect(store.activeTheme).toBe('imperial')
    expect(document.documentElement.dataset.theme).toBe('imperial')

    store.activateArmy('army-2')
    expect(store.activeTheme).toBe('cozy')
    expect(document.documentElement.dataset.theme).toBe('cozy')

    store.activateArmy('army-1')
    expect(store.activeTheme).toBe('imperial')
  })

  it('persists army themes in local storage', () => {
    const store = useThemeStore()
    store.setArmyTheme('army-1', 'imperial')

    expect(JSON.parse(localStorage.getItem('hobbyplanner.army-themes.v1') ?? '{}')).toEqual({ 'army-1': 'imperial' })
  })
})
