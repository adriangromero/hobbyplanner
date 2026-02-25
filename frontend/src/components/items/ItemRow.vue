<template>
  <tr class="border-b hover:bg-gray-50">
    <td class="p-3">{{ item.name }}</td>
    <td class="p-3">{{ formatHours(item.estimatedHours) }}</td>
    <td class="p-3">{{ item.totalSessions }}
      <button
        @click="openModal"
        class="text-gray-400 hover:text-gray-700 transition-colors"
        title="Ver sesiones"
      >
        👁
      </button>
    </td>
    <td class="p-3">{{ formatHours(item.totalHours) }}</td>
  </tr>

  <Teleport to="body">
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">
            Sesiones — {{ item.name }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-700">
            ✕
          </button>
        </div>

        <!-- Sin sesiones -->
        <div v-if="item.sessions.length === 0" class="text-gray-400 text-sm py-4 text-center">
          No hay sesiones registradas
        </div>

        <!-- Lista sesiones -->
        <div v-else class="space-y-2 max-h-80 overflow-y-auto">
          <div
            v-for="session in item.sessions"
            :key="session.id"
            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg text-sm"
          >
            <div class="text-gray-600">
              <span>{{ formatDate(session.startedAt) }}</span>
              <span class="mx-2 text-gray-400">→</span>
              <span>{{ session.endedAt ? formatDate(session.endedAt) : 'En curso' }}</span>
            </div>
            <span class="font-medium text-gray-800">
              {{ formatHours(session.durationHours) }}
            </span>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="item.sessions.length > 0" class="mt-4 pt-4 border-t flex justify-end">
          <span class="text-sm text-gray-500">
            Total: <strong>{{ formatHours(totalHours) }}</strong>
          </span>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Session {
  id: string
  startedAt: string
  endedAt: string | null
  durationHours: number
}

interface Item {
  id: string
  name: string
  estimatedHours: number
  totalSessions: number
  sessions: Session[]
}

const props = defineProps<{
  item: Item
}>()

const showModal = ref(false)

const totalHours = computed(() =>
  props.item.sessions.reduce((sum, s) => sum + s.durationHours, 0)
)

function openModal() {
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatHours(hours: number): string {
  const h = Math.floor(hours)
  const m = Math.round((hours - h) * 60)
  return m > 0 ? `${h}h ${m}m` : `${h}h`
}
</script>