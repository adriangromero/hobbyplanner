<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="$emit('close')"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">

        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Nuevo Item</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700">✕</button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Space Marine, Chaos Warrior..."
              class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Horas estimadas</label>
            <input
              v-model="form.estimatedHours"
              type="number"
              min="0.5"
              step="0.5"
              placeholder="2.5"
              class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
          </div>
          <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>
        </div>

        <div class="flex gap-2 justify-end mt-6">
          <button
            @click="$emit('close')"
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition"
          >
            Cancelar
          </button>
          <button
            @click="handleCreate"
            :disabled="loading"
            class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition disabled:opacity-50"
          >
            {{ loading ? 'Creando...' : 'Crear Item' }}
          </button>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import { useToast } from '@/composables/useToast'
import api from '@/api/axios'

const emit = defineEmits<{ close: [] }>()

const projectStore = useProjectStore()
const toast        = useToast()

const loading = ref(false)
const error   = ref<string | null>(null)
const form    = ref({ name: '', estimatedHours: '' })

async function handleCreate() {
  error.value = null

  if (!form.value.name.trim()) {
    error.value = 'El nombre es obligatorio'
    return
  }

  if (!form.value.estimatedHours || parseFloat(form.value.estimatedHours) <= 0) {
    error.value = 'Las horas estimadas deben ser mayores a 0'
    return
  }

  loading.value = true

  try {
    const { data } = await api.post('/items', {
      projectId:      projectStore.currentProject!.id,
      name:           form.value.name.trim(),
      estimatedHours: parseFloat(form.value.estimatedHours),
    })

    projectStore.addItem({
      id:             data.id,
      name:           data.name,
      estimatedHours: data.estimatedHours,
      status:         data.status ?? 'pending',
      createdAt:      data.createdAt,
      totalSessions:  0,
      totalHours:     0,
      openSession:    null,
    })

    toast.success(`Item "${data.name}" creado correctamente`)
    emit('close')

  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'Error al crear el item'
  } finally {
    loading.value = false
  }
}
</script>