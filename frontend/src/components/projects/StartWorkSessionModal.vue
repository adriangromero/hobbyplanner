<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="$emit('close')"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">¿Qué item vas a pintar?</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700">✕</button>
        </div>

        <!-- Timer corriendo warning -->
        <div v-if="timer.isRunning" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
          ⚠️ Se parará la sesión de <strong>{{ timer.activeItemName }}</strong> automáticamente.
        </div>

        <!-- Lista items -->
        <div class="space-y-2 max-h-72 overflow-y-auto">
          <button
            v-for="item in items"
            :key="item.id"
            @click="handleStart(item)"
            :disabled="loading"
            class="w-full text-left p-3 rounded-lg border hover:border-red-400 hover:bg-red-50 transition-colors disabled:opacity-50"
            :class="timer.activeItemId === item.id ? 'border-red-400 bg-red-50' : 'border-gray-200'"
          >
            <div class="flex justify-between items-center">
              <span class="font-medium text-gray-800">{{ item.name }}</span>
              <span class="text-xs text-gray-400">{{ item.estimatedHours }}h est.</span>
            </div>
            <span
              v-if="timer.activeItemId === item.id"
              class="text-xs text-red-500 font-medium"
            >
              🎨 En curso — {{ timer.elapsedFormatted }}
            </span>
          </button>
        </div>

        <!-- Error -->
        <div v-if="error" class="mt-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg">
          {{ error }}
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTimerStore } from '@/stores/timerStore'
import { useProjectStore } from '@/stores/projectStore'

interface Item {
  id: string
  name: string
  estimatedHours: number
}

const props = defineProps<{
  items: Item[]
}>()

const emit = defineEmits<{
  close: []
}>()

const timer        = useTimerStore()
const projectStore = useProjectStore()
const loading      = ref(false)
const error        = ref<string | null>(null)

async function handleStart(item: Item) {
  loading.value = true
  error.value   = null

  try {
    await timer.start(item.id, item.name, projectStore.currentProject!.id)
    emit('close')

  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'Error al iniciar la sesión'
  } finally {
    loading.value = false
  }
}
</script>