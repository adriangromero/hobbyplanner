<template>
  <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-2.5">

        <!-- Left: Logo + Nav -->
        <div class="flex flex-col justify-center gap-0.5">
          <router-link to="/projects" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center shrink-0">
              <span class="text-white font-bold text-lg">H</span>
            </div>
            <span class="font-bold text-xl text-gray-900 hidden sm:block">HobbyPlanner</span>
          </router-link>
          <div class="hidden md:flex items-center gap-1 pl-10">
            <router-link
              to="/projects"
              class="text-xs font-medium text-gray-500 hover:text-blue-600 transition-colors"
              active-class="text-blue-600"
            >
              Proyectos
            </router-link>
          </div>
        </div>

        <!-- Center: Timer activo -->
        <div
          v-if="timer.isRunning"
          class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-full px-5 py-2 animate-pulse-subtle"
        >
          <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse" />
          <span class="text-sm font-medium text-red-700 hidden sm:block max-w-[180px] truncate">
            {{ timer.activeItemName }}
          </span>
          <span class="font-mono font-bold text-red-600 text-sm tracking-wide">
            {{ timer.elapsedFormatted }}
          </span>
          <button
            @click="handleStop"
            :disabled="loading"
            class="ml-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1 rounded-full transition disabled:opacity-50"
          >
            {{ loading ? '...' : 'Parar' }}
          </button>
        </div>
        <div v-else />

        <!-- Right: User info + avatar -->
        <div class="relative">
          <button
            @click="menuOpen = !menuOpen"
            class="flex items-center gap-3 p-1.5 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <div class="hidden sm:flex flex-col items-end">
              <span class="text-sm font-semibold text-gray-900 leading-tight">{{ userName }}</span>
              <span class="text-xs text-gray-400 leading-tight">{{ userEmail }}</span>
            </div>
            <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center ring-2 ring-white shadow-sm">
              <span class="text-white text-sm font-semibold">{{ userInitials }}</span>
            </div>
            <svg
              class="w-4 h-4 text-gray-400 transition-transform hidden sm:block"
              :class="{ 'rotate-180': menuOpen }"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown -->
          <Transition name="dropdown">
            <div
              v-if="menuOpen"
              v-click-outside="closeMenu"
              class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
            >
              <div class="px-4 py-3 border-b border-gray-100 sm:hidden">
                <p class="text-sm font-semibold text-gray-900">{{ userName }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ userEmail }}</p>
              </div>

              <button
                @click="handleLogout"
                class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors w-full"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Cerrar sesion
              </button>
            </div>
          </Transition>
        </div>

      </div>
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

const timer        = useTimerStore()
const projectStore = useProjectStore()
const authStore    = useAuthStore()
const toast        = useToast()
const { loading, loadingMessage, run } = useBlockingAction()

const menuOpen = ref(false)
const user     = computed(() => authStore.user)

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

function handleLogout() {
  closeMenu()
  authStore.logout()
}

async function handleStop() {
  const itemId = timer.activeItemId

  await run('Finalizando sesion...', async () => {
    const session = await timer.stop()

    if (session && itemId) {
      projectStore.addSessionToItem(itemId, session)
      toast.success(`Sesion finalizada - ${formatHours(session.durationHours)}`)
    }
  })
}

const vClickOutside = {
  mounted(el: HTMLElement, binding: { value: () => void }) {
    const handler = (event: Event) => {
      if (event.target && !(el === event.target || el.contains(event.target as Node))) {
        binding.value()
      }
    }
    ;(el as any)._clickOutside = handler
    document.addEventListener('click', handler)
  },
  unmounted(el: HTMLElement) {
    const handler = (el as any)._clickOutside
    if (handler) document.removeEventListener('click', handler)
  },
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@keyframes pulse-subtle {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.85; }
}
.animate-pulse-subtle {
  animation: pulse-subtle 3s ease-in-out infinite;
}
</style>
