<template>
  <div
    @click="handleCardClick"
    class="p-4 bg-white rounded-lg shadow-sm border hover:shadow-md cursor-pointer transition"
    :class="{ 'border-green-200 bg-green-50/50': project.status === 'completed' }"
  >

    <!-- Vista normal -->
    <template v-if="!editingProject && !deletingProject">
      <div class="flex justify-between items-start">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <h2 class="text-xl font-semibold">{{ project.name }}</h2>
            <span
              v-if="project.status === 'completed'"
              class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700"
            >
              Completado
            </span>
          </div>
          <p class="text-gray-600 text-sm">{{ project.description || 'Sin descripción' }}</p>
          <p class="text-gray-400 text-xs mt-1">{{ formatDate(project.createdAt) }}</p>
        </div>
        <div class="flex items-center gap-2 ml-3 shrink-0" @click.stop>
          <button
            @click="startEdit"
            class="text-blue-400 hover:text-blue-600 transition-colors"
            title="Editar proyecto"
          >
            ✏️
          </button>
          <button
            @click="deletingProject = true"
            class="text-red-400 hover:text-red-600 transition-colors"
            title="Eliminar proyecto"
          >
            🗑️
          </button>
        </div>
      </div>
    </template>

    <!-- Editar inline -->
    <template v-else-if="editingProject">
      <div class="space-y-3" @click.stop>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
          <input
            v-model="editForm.name"
            type="text"
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
          <textarea
            v-model="editForm.description"
            rows="2"
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
          />
        </div>
        <div v-if="editError" class="text-red-500 text-xs">{{ editError }}</div>
        <div class="flex gap-2 justify-end">
          <button
            @click="cancelEdit"
            :disabled="loading"
            class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition disabled:opacity-50"
          >
            Cancelar
          </button>
          <button
            @click="handleUpdate"
            :disabled="loading"
            class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition disabled:opacity-50"
          >
            Guardar
          </button>
        </div>
      </div>
    </template>

    <!-- Confirmar eliminar -->
    <template v-else-if="deletingProject">
      <div class="flex justify-between items-center" @click.stop>
        <span class="text-sm text-red-700 font-medium">¿Eliminar "{{ project.name }}"?</span>
        <div class="flex gap-2">
          <button
            @click="deletingProject = false"
            :disabled="loading"
            class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition disabled:opacity-50"
          >
            No
          </button>
          <button
            @click="handleDelete"
            :disabled="loading"
            class="px-3 py-1 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition disabled:opacity-50"
          >
            Sí
          </button>
        </div>
      </div>
    </template>

    <BlockingOverlay :active="loading" :message="loadingMessage" :detail="project.name" />

  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import { useBlockingAction } from '@/composables/useBlockingAction'
import { useToast } from '@/composables/useToast'
import { formatDate } from '@/utils/format'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'
import api from '@/api/axios'

interface Project {
  id:           string
  name:         string
  description?: string
  status:       'active' | 'completed'
  createdAt:    string
}

const props = defineProps<{ project: Project }>()

const router       = useRouter()
const projectStore = useProjectStore()
const toast        = useToast()
const { loading, loadingMessage, run } = useBlockingAction()

const editingProject  = ref(false)
const deletingProject = ref(false)
const editError       = ref<string | null>(null)
const editForm        = ref({ name: '', description: '' })

function handleCardClick() {
  if (!editingProject.value && !deletingProject.value) {
    router.push(`/projects/${props.project.id}`)
  }
}

function startEdit() {
  editingProject.value  = true
  deletingProject.value = false
  editError.value       = null
  editForm.value = {
    name:        props.project.name,
    description: props.project.description ?? '',
  }
}

function cancelEdit() {
  editingProject.value = false
  editError.value      = null
  editForm.value       = { name: '', description: '' }
}

async function handleUpdate() {
  editError.value = null

  if (!editForm.value.name.trim()) {
    editError.value = 'El nombre es obligatorio'
    return
  }

  await run('Guardando proyecto...', async () => {
    try {
      const { data } = await api.put(`/projects/${props.project.id}`, {
        name:        editForm.value.name.trim(),
        description: editForm.value.description.trim(),
      })

      projectStore.updateProject({
        id:          data.id,
        name:        data.name,
        description: data.description,
      })

      cancelEdit()
      toast.success(`Proyecto "${data.name}" actualizado`)

    } catch (e: any) {
      editError.value = e.response?.data?.error ?? 'Error al actualizar'
    }
  })
}

async function handleDelete() {
  await run('Eliminando proyecto...', async () => {
    try {
      await api.delete(`/projects/${props.project.id}`)
      projectStore.removeProject(props.project.id)
      toast.success(`Proyecto "${props.project.name}" eliminado`)
    } catch {
      toast.error('Error al eliminar el proyecto')
    }
  })
}
</script>
