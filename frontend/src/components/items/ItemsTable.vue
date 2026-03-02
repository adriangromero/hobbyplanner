<template>
  <div>
    <div class="flex justify-between items-center mb-3">
      <h2 class="text-xl font-semibold">Items</h2>
      <button
        @click="showCreateModal = true"
        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
      >
        + Add Item
      </button>
    </div>

    <table class="w-full bg-white rounded-lg shadow-sm border">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="p-3">Name</th>
          <th class="p-3">Estimated</th>
          <th class="p-3">Sessions</th>
          <th class="p-3">Worked</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <ItemRow
          v-for="item in items"
          :key="item.id"
          :item="item"
        />
        <tr v-if="items.length === 0">
          <td colspan="5" class="p-6 text-center text-gray-400 text-sm">
            No items yet. Add one to get started.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal crear item -->
    <Teleport to="body">
      <div
        v-if="showCreateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="closeCreateModal"
      >
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">

          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Nuevo Item</h3>
            <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-700">✕</button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
              <input
                v-model="createForm.name"
                type="text"
                placeholder="Space Marine, Chaos Warrior..."
                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Horas estimadas</label>
              <input
                v-model="createForm.estimatedHours"
                type="number"
                min="0.5"
                step="0.5"
                placeholder="2.5"
                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
            </div>
            <div v-if="createError" class="text-red-500 text-sm">{{ createError }}</div>
          </div>

          <div class="flex gap-2 justify-end mt-6">
            <button
              @click="closeCreateModal"
              class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition"
            >
              Cancelar
            </button>
            <button
              @click="handleCreate"
              :disabled="createLoading"
              class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition disabled:opacity-50"
            >
              {{ createLoading ? 'Creando...' : 'Crear Item' }}
            </button>
          </div>

        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import { useToast } from '@/composables/useToast'
import api from '@/api/axios'
import ItemRow from './ItemRow.vue'

interface Session {
  id:            string
  startedAt:     string
  endedAt:       string | null
  durationHours: number
}

interface Item {
  id:             string
  name:           string
  estimatedHours: number
  totalSessions:  number
  sessions:       Session[]
}

defineProps<{ items: Item[] }>()

const projectStore = useProjectStore()
const toast        = useToast()

const showCreateModal = ref(false)
const createLoading   = ref(false)
const createError     = ref<string | null>(null)
const createForm      = ref({ name: '', estimatedHours: '' })

function closeCreateModal() {
  showCreateModal.value = false
  createError.value     = null
  createForm.value      = { name: '', estimatedHours: '' }
}

async function handleCreate() {
  createError.value = null

  if (!createForm.value.name.trim()) {
    createError.value = 'El nombre es obligatorio'
    return
  }

  if (!createForm.value.estimatedHours || parseFloat(createForm.value.estimatedHours) <= 0) {
    createError.value = 'Las horas estimadas deben ser mayores a 0'
    return
  }

  createLoading.value = true

  try {
    const { data } = await api.post('/items', {
      projectId:      projectStore.currentProject!.id,
      name:           createForm.value.name.trim(),
      estimatedHours: parseFloat(createForm.value.estimatedHours),
    })

    projectStore.addItem({
      id:             data.id,
      name:           data.name,
      estimatedHours: data.estimatedHours,
      totalSessions:  0,
      sessions:       [],
    })

    closeCreateModal()
    toast.success(`Item "${data.name}" creado correctamente`)

  } catch (e: any) {
    createError.value = e.response?.data?.error ?? 'Error al crear el item'
  } finally {
    createLoading.value = false
  }
}
</script>