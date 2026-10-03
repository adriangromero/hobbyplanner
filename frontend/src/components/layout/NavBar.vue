<template>
  <nav class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex min-h-[68px] items-center justify-between gap-3 py-2">
        <div class="flex min-w-0 items-center gap-7">
          <router-link to="/armies" class="flex shrink-0 items-center gap-2" aria-label="HobbyPlanner, ejércitos">
            <span class="app-mark flex h-9 w-9 items-center justify-center rounded-xl text-lg font-bold text-white shadow-sm">H</span>
            <span class="hidden font-bold tracking-tight text-gray-900 sm:block">HobbyPlanner</span>
          </router-link>

          <div class="hidden items-center gap-1 md:flex">
            <router-link to="/armies" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900" active-class="bg-gray-100 text-gray-900">
              Ejércitos
            </router-link>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
          <div v-if="timer.isRunning" class="flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-2 sm:gap-3 sm:px-4">
            <span class="h-2 w-2 animate-pulse rounded-full bg-red-500" aria-hidden="true" />
            <span class="hidden max-w-[150px] truncate text-sm font-medium text-red-800 sm:block">{{ timer.activeItemName }}</span>
            <span class="font-mono text-sm font-bold tabular-nums text-red-700">{{ timer.elapsedFormatted }}</span>
            <button
              type="button"
              @click="handleStop"
              :disabled="loading"
              class="rounded-full bg-red-700 px-3 py-1 text-xs font-semibold text-white transition hover:bg-red-800 disabled:opacity-50"
              aria-label="Finalizar sesión activa"
            >
              {{ loading ? '…' : 'Parar' }}
            </button>
          </div>

          <div class="relative">
            <button
              type="button"
              @click="menuOpen = !menuOpen"
              :aria-expanded="menuOpen"
              aria-haspopup="true"
              class="flex items-center gap-2 rounded-xl p-1.5 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 sm:gap-3 sm:pl-3"
            >
              <span class="hidden min-w-0 text-right sm:block">
                <span class="block max-w-40 truncate text-sm font-semibold leading-tight text-gray-900">{{ userName }}</span>
                <span class="block max-w-40 truncate text-xs leading-tight text-gray-500">{{ userEmail }}</span>
              </span>
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-700 to-blue-700 text-sm font-semibold text-white ring-2 ring-white shadow-sm">{{ userInitials }}</span>
              <svg class="hidden h-4 w-4 text-gray-500 transition-transform sm:block" :class="{ 'rotate-180': menuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
              </svg>
            </button>

            <Transition name="dropdown">
              <div
                v-if="menuOpen"
                v-click-outside="closeMenu"
                role="menu"
                aria-label="Menú de usuario"
                @keydown.esc="closeMenu"
                class="absolute right-0 mt-2 w-64 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl"
              >
                <div class="border-b border-gray-100 px-4 py-3">
                  <p class="truncate text-sm font-semibold text-gray-900">{{ userName }}</p>
                  <p class="truncate text-xs text-gray-500">{{ userEmail }}</p>
                </div>
                <button
                  type="button"
                  role="menuitem"
                  @click="handleLogout"
                  class="mt-1 flex w-full items-center gap-2 border-t border-gray-100 px-4 py-2.5 text-left text-sm font-medium text-red-700 transition hover:bg-red-50"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1" />
                  </svg>
                  Cerrar sesión
                </button>
              </div>
            </Transition>
          </div>

          <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 md:hidden"
            :aria-expanded="mobileNavOpen"
            aria-controls="mobile-navigation"
            @click="mobileNavOpen = !mobileNavOpen"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mobileNavOpen ? 'M6 18 18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'" />
            </svg>
            Menú
          </button>
        </div>
      </div>

      <Transition name="dropdown">
        <div v-if="mobileNavOpen" id="mobile-navigation" v-click-outside="closeMobileNav" class="space-y-1 border-t border-gray-200 py-3 md:hidden" @keydown.esc="closeMobileNav">
          <router-link to="/armies" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100" active-class="bg-gray-100 text-gray-900" @click="mobileNavOpen = false">Ejércitos</router-link>
        </div>
      </Transition>
    </div>

    <BlockingOverlay :active="loading" :message="loadingMessage" />
  </nav>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTimerStore } from '@/stores/timerStore'
import { useProjectStore } from '@/stores/projectStore'
import { useAuthStore } from '@/stores/authStore'
import { useBlockingAction } from '@/composables/useBlockingAction'
import { useToast } from '@/composables/useToast'
import { formatHours } from '@/utils/format'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'

const timer = useTimerStore()
const projectStore = useProjectStore()
const authStore = useAuthStore()
const toast = useToast()
const { loading, loadingMessage, run } = useBlockingAction()

const menuOpen = ref(false)
const mobileNavOpen = ref(false)
const user = computed(() => authStore.user)
const userName = computed(() => user.value?.name ?? 'Usuario')
const userEmail = computed(() => user.value?.email ?? '')
const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  const names = user.value.name.trim().split(' ')
  if (names.length >= 2) return (names[0][0] + names[1][0]).toUpperCase()
  return names[0][0].toUpperCase()
})

function closeMenu() {
  menuOpen.value = false
}

function closeMobileNav() {
  mobileNavOpen.value = false
}

function handleLogout() {
  closeMenu()
  authStore.logout()
}

async function handleStop() {
  await run('Finalizando sesión…', async () => {
    const result = await timer.stop()
    if (!result) {
      toast.error('Error al guardar la sesión')
      return
    }
    if (result.kind === 'work-session') projectStore.addSessionToItem(result.itemId, result.session)
    else {
      projectStore.applyPaintingSession(result.session)
      await projectStore.refreshPaintingEstimates()
    }
    toast.success(`Sesión finalizada — ${formatHours(result.session.durationHours)}`)
  })
}

const vClickOutside = {
  mounted(el: HTMLElement, binding: { value: () => void }) {
    const handler = (event: Event) => {
      if (event.target && !(el === event.target || el.contains(event.target as Node))) binding.value()
    }
    ;(el as any)._clickOutside = handler
    setTimeout(() => document.addEventListener('click', handler), 0)
  },
  unmounted(el: HTMLElement) {
    const handler = (el as any)._clickOutside
    if (handler) document.removeEventListener('click', handler)
  },
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from,
.dropdown-leave-to { opacity: 0; transform: translateY(-5px); }
</style>
