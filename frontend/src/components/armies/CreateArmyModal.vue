<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="$emit('close')"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">

        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Crear ejército</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700">✕</button>
        </div>

        <div class="space-y-4">
          <div>
            <label for="army-name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del ejército</label>
            <input
              id="army-name"
              v-model="form.name"
              type="text"
              placeholder="Ej. Talabheim, Guardia del Bosque…"
              class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
          </div>
          <div>
            <label for="army-description" class="block text-sm font-medium text-gray-700 mb-1">Descripción <span class="font-normal text-gray-500">(opcional)</span></label>
            <textarea
              id="army-description"
              v-model="form.description"
              placeholder="Facción, colección o notas sobre el ejército…"
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
            {{ loading ? 'Creando…' : 'Crear ejército' }}
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

const emit = defineEmits<{ close: [] }>()

const projectStore = useProjectStore()
const toast        = useToast()

const loading = ref(false)
const error   = ref<string | null>(null)
const form    = ref({ name: '', description: '' })

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
      'army',
    )

    projectStore.addProject({
      id:             data.id,
      name:           data.name,
      description:    data.description,
      status:         data.status ?? 'active',
      type:           'army',
      createdAt:      data.createdAt,
      unitCount:      data.unitCount ?? 0,
      miniatureCount: data.miniatureCount ?? 0,
    })

    toast.success(`Ejército "${data.name}" creado correctamente`)
    emit('close')

  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'Error al crear el ejército'
  } finally {
    loading.value = false
  }
}
</script>
