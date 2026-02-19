<template>
  <div v-if="isRunning" class="bg-blue-600 text-white px-6 py-3 flex items-center gap-4">
    <span class="font-semibold">
      🎨 Pintando: {{ activeItem?.name ?? 'Item desconocido' }}
    </span>

    <span class="text-lg font-mono">
      {{ elapsedFormatted }}
    </span>

    <button
      class="ml-auto bg-white text-blue-600 px-4 py-1 rounded"
      @click="stop"
    >
      STOP
    </button>
  </div>

  <div v-else class="bg-gray-100 px-6 py-3 flex items-center gap-4">
    <select
      v-model="selectedItemId"
      class="border px-2 py-1 rounded"
    >
      <option disabled value="">Selecciona item</option>
      <option
        v-for="item in items"
        :key="item.id"
        :value="item.id"
      >
        {{ item.name }}
      </option>
    </select>

    <button
      class="bg-blue-600 text-white px-4 py-1 rounded"
      @click="start"
      :disabled="!selectedItemId"
    >
      START
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTimerStore } from '@/stores/timerStore'
import { useProjectStore } from '@/stores/projectStore'

const timer = useTimerStore()
const project = useProjectStore()

// 🔥 ESTA ES LA CLAVE: ref(), NO const
const selectedItemId = ref<string>('')

const items = computed(() => project.items)
const activeItem = computed(() =>
  project.items.find(i => i.id === timer.activeItemId) ?? null
)

const isRunning = computed(() => timer.isRunning)
const elapsedFormatted = computed(() => timer.elapsedFormatted)

function start() {
  if (!selectedItemId.value) return
  timer.start(selectedItemId.value, project.currentProject.id)
}

function stop() {
  timer.stop()
}
</script>
