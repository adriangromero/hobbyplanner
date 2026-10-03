import { defineStore } from 'pinia'

export type ArmyTheme = 'cozy' | 'imperial'

const STORAGE_KEY = 'hobbyplanner.army-themes.v1'

function readArmyThemes(): Record<string, ArmyTheme> {
  try {
    const value = localStorage.getItem(STORAGE_KEY)
    if (!value) return {}
    const parsed = JSON.parse(value) as Record<string, unknown>
    return Object.fromEntries(
      Object.entries(parsed).filter((entry): entry is [string, ArmyTheme] => entry[1] === 'cozy' || entry[1] === 'imperial'),
    )
  } catch {
    return {}
  }
}

export const useThemeStore = defineStore('theme', {
  state: () => ({
    armyThemes: readArmyThemes(),
    activeArmyId: null as string | null,
  }),

  getters: {
    activeTheme: (state): ArmyTheme => state.activeArmyId
      ? state.armyThemes[state.activeArmyId] ?? 'cozy'
      : 'cozy',
  },

  actions: {
    activateArmy(projectId: string | null) {
      this.activeArmyId = projectId
      this.applyTheme()
    },

    setArmyTheme(projectId: string, theme: ArmyTheme) {
      this.armyThemes = { ...this.armyThemes, [projectId]: theme }
      localStorage.setItem(STORAGE_KEY, JSON.stringify(this.armyThemes))
      if (this.activeArmyId === projectId) this.applyTheme()
    },

    applyTheme() {
      document.documentElement.dataset.theme = this.activeTheme
    },
  },
})
