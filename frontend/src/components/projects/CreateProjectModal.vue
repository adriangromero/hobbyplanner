<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="$emit('close')"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">

        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Nuevo Proyecto</h3>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de proyecto</label>
            <select v-model="form.type" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
              <option value="general">Proyecto general</option>
              <option value="army">Ejército</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Un ejército también es un proyecto; añade inventario y progreso de pintado desde su detalle.</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea
              v-model="form.description"
              placeholder="Describe el proyecto..."
              rows="3"
              class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
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
            {{ loading ? 'Creando...' : 'Crear Proyecto' }}
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
import { projectApi } from '@/api/projectApi'

const props = withDefaults(defineProps<{ initialType?: 'general' | 'army' }>(), { initialType: 'general' })
const emit = defineEmits<{ close: [] }>()

const projectStore = useProjectStore()
const toast        = useToast()

const loading = ref(false)
const error   = ref<string | null>(null)
const form    = ref<{ name: string; description: string; type: 'general' | 'army' }>({ name: '', description: '', type: props.initialType })

async function handleCreate() {
  error.value = null

  if (!form.value.name.trim()) {
    error.value = 'El nombre es obligatorio'
    return
  }

  loading.value = true

  try {
    const data = await projectApi.create(
      form.value.name.trim(),
      form.value.description.trim(),
      form.value.type,
    )

    projectStore.addProject({
      id:             data.id,
      name:           data.name,
      description:    data.description,
      status:         data.status ?? 'active',
      type:           data.type ?? form.value.type,
      createdAt:      data.createdAt,
    })

    toast.success(`Proyecto "${data.name}" creado correctamente`)
    emit('close')

  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'Error al crear el proyecto'
  } finally {
    loading.value = false
  }
}
</script>
