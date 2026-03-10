<template>
  <div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold mb-2">Inventario</h1>
    <p class="text-gray-500 text-sm mb-6">Todas tus miniaturas y items en un solo lugar</p>

    <div v-if="store.loading" class="text-gray-500">Cargando...</div>

    <template v-else>
      <!-- Filtros -->
      <div class="flex gap-2 mb-4">
        <button
          v-for="f in filters"
          :key="f.value"
          @click="activeFilter = f.value"
          class="px-3 py-1.5 text-sm rounded-full border transition-colors"
          :class="activeFilter === f.value
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400'"
        >
          {{ f.label }}
          <span class="ml-1 text-xs opacity-75">({{ f.count }})</span>
        </button>
      </div>

      <!-- Tabla -->
      <table class="w-full bg-white rounded-lg shadow-sm border">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-3 w-10"></th>
            <th class="p-3">Nombre</th>
            <th class="p-3">Proyecto</th>
            <th class="p-3">Estimadas</th>
            <th class="p-3">Trabajadas</th>
            <th class="p-3">Creado</th>
            <th class="p-3">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in filteredItems"
            :key="item.id"
            class="border-b hover:bg-gray-50"
            :class="{ 'opacity-50 bg-green-50': item.status === 'completed' }"
          >
            <td class="p-3">
              <div class="flex items-center gap-2">
                <template v-if="confirmingId === item.id">
                  <span class="text-xs font-medium" :class="item.status === 'completed' ? 'text-gray-600' : 'text-green-700'">
                    {{ item.status === 'completed' ? '¿Reactivar?' : '¿Completar?' }}
                  </span>
                  <button
                    @click="toggleStatus(item)"
                    :disabled="togglingId === item.id"
                    class="text-xs bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded transition disabled:opacity-50"
                  >
                    Sí
                  </button>
                  <button
                    @click="confirmingId = null"
                    class="text-xs text-gray-500 hover:text-gray-700 transition"
                  >
                    No
                  </button>
                </template>
                <button
                  v-else
                  @click="confirmingId = item.id"
                  :disabled="togglingId === item.id"
                  class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors disabled:opacity-50"
                  :class="item.status === 'completed'
                    ? 'bg-green-500 border-green-500 text-white'
                    : 'border-gray-300 hover:border-green-400'"
                  :title="item.status === 'completed' ? 'Marcar como pendiente' : 'Marcar como completado'"
                >
                  <span v-if="item.status === 'completed'" class="text-xs">&#10003;</span>
                </button>
              </div>
            </td>
            <td class="p-3">
              <span :class="{ 'line-through text-gray-400': item.status === 'completed' }">
                {{ item.name }}
              </span>
            </td>
            <td class="p-3">
              <router-link
                :to="`/projects/${item.projectId}`"
                class="text-blue-600 hover:underline text-sm"
              >
                {{ item.projectName }}
              </router-link>
            </td>
            <td class="p-3 text-sm">{{ item.estimatedHours }}h</td>
            <td class="p-3 text-sm text-gray-600">{{ formatHours(item.totalHours) }}</td>
            <td class="p-3 text-sm text-gray-400">{{ formatDate(item.createdAt) }}</td>
            <td class="p-3">
              <span
                class="text-xs font-medium px-2 py-0.5 rounded-full"
                :class="statusBadge(item.status)"
              >
                {{ statusLabel(item.status) }}
              </span>
            </td>
          </tr>
          <tr v-if="filteredItems.length === 0">
            <td colspan="7" class="p-6 text-center text-gray-400 text-sm">
              No hay items con este filtro
            </td>
          </tr>
        </tbody>
      </table>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import type { InventoryItem } from '@/stores/projectStore'
import { useToast } from '@/composables/useToast'
import { formatHours, formatDate } from '@/utils/format'
import api from '@/api/axios'

const store = useProjectStore()
const toast = useToast()
const activeFilter = ref('all')
const togglingId    = ref<string | null>(null)
const confirmingId  = ref<string | null>(null)

onMounted(() => store.loadInventory())

const filters = computed(() => {
  const items = store.inventory
  return [
    { label: 'Todos',       value: 'all',         count: items.length },
    { label: 'Pendientes',  value: 'pending',     count: items.filter(i => i.status === 'pending').length },
    { label: 'En progreso', value: 'in_progress', count: items.filter(i => i.status === 'in_progress').length },
    { label: 'Completados', value: 'completed',   count: items.filter(i => i.status === 'completed').length },
  ]
})

const filteredItems = computed(() => {
  if (activeFilter.value === 'all') return store.inventory
  return store.inventory.filter(i => i.status === activeFilter.value)
})

function statusLabel(status: string): string {
  switch (status) {
    case 'completed':   return 'Completado'
    case 'in_progress': return 'En progreso'
    default:            return 'Pendiente'
  }
}

function statusBadge(status: string): string {
  switch (status) {
    case 'completed':   return 'bg-green-100 text-green-700'
    case 'in_progress': return 'bg-blue-100 text-blue-700'
    default:            return 'bg-gray-100 text-gray-600'
  }
}

async function toggleStatus(item: InventoryItem) {
  confirmingId.value = null
  togglingId.value = item.id
  try {
    const { data } = await api.put(`/items/${item.id}/toggle-status`)
    item.status = data.status
    toast.success(
      data.status === 'completed'
        ? `"${item.name}" completado`
        : `"${item.name}" reactivado`
    )
  } catch {
    toast.error('Error al cambiar el estado')
  } finally {
    togglingId.value = null
  }
}
</script>
