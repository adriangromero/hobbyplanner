<template>
  <div>
    <div class="flex justify-between items-center mb-3">
      <h2 class="text-xl font-semibold">Items</h2>
      <button
        @click="showCreateModal = true"
        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
      >
        + Añadir item
      </button>
    </div>

    <table class="w-full bg-white rounded-lg shadow-sm border">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="p-3 w-10"></th>
          <th class="p-3">Nombre</th>
          <th class="p-3">Estimadas</th>
          <th class="p-3">Sesiones</th>
          <th class="p-3">Trabajadas</th>
          <th class="p-3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <ItemRow
          v-for="item in items"
          :key="item.id"
          :item="item"
        />
        <tr v-if="items.length === 0">
          <td colspan="6" class="p-6 text-center text-gray-400 text-sm">
            Aún no hay items. Añade uno para empezar.
          </td>
        </tr>
      </tbody>
    </table>

    <CreateItemModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import ItemRow from './ItemRow.vue'
import CreateItemModal from './CreateItemModal.vue'

interface Item {
  id:             string
  name:           string
  estimatedHours: number
  status:         'pending' | 'in_progress' | 'completed'
  createdAt:      string
  totalSessions:  number
  totalHours:     number
  openSession:    { id: string; startedAt: string } | null
}

defineProps<{ items: Item[] }>()

const showCreateModal = ref(false)
</script>
