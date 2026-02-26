<template>
  <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">

        <!-- Left: Logo + Navigation -->
        <div class="flex items-center gap-8">
          <router-link to="/projects" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">H</span>
            </div>
            <span class="font-bold text-xl text-gray-900 hidden sm:block">HobbyPlanner</span>
          </router-link>

          <div class="hidden md:flex items-center gap-1">
            <NavLink to="/projects" icon="📊">Projects</NavLink>
          </div>
        </div>

        <!-- Center: Timer activo -->
        <div
          v-if="timer.isRunning"
          class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2"
        >
          <span class="text-red-600">🎨</span>
          <span class="text-sm font-medium text-red-700 hidden sm:block">
            {{ timer.activeItemName }}
          </span>
          <span class="font-mono font-bold text-red-600 text-sm">
            {{ timer.elapsedFormatted }}
          </span>
          <button
            @click="handleStop"
            :disabled="stopping"
            class="ml-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1 rounded transition disabled:opacity-50"
          >
            {{ stopping ? '...' : '⏹ Stop' }}
          </button>
        </div>

        <!-- Right: User Menu -->
        <div class="flex items-center gap-4">
          <UserMenu />
        </div>

      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTimerStore } from '@/stores/timerStore'
import { useProjectStore } from '@/stores/projectStore'
import NavLink from './NavLink.vue'
import UserMenu from './UserMenu.vue'

const timer        = useTimerStore()
const projectStore = useProjectStore()
const stopping     = ref(false)

async function handleStop() {
  stopping.value = true

  await timer.stop()

  if (projectStore.currentProject) {
    await projectStore.loadProject(projectStore.currentProject.id)
  }

  stopping.value = false
}
</script>