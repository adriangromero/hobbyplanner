<template>
  <article
    class="p-4 bg-white rounded-xl shadow-sm border hover:shadow-md transition"
    :class="{ 'border-green-200 bg-green-50/50': project.status === 'completed' }"
  >

    <!-- Vista normal -->
    <template v-if="!editingProject && !deletingProject">
      <div class="grid grid-cols-[minmax(0,1fr)_auto] sm:grid-cols-[minmax(0,1fr)_160px_125px_auto] items-center gap-x-5 gap-y-3">
        <button type="button" class="min-w-0 text-left rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" @click="handleCardClick">
          <div class="flex items-center gap-2">
            <h2 class="text-lg font-semibold truncate">{{ project.name }}</h2>
            <span v-if="project.type === 'army'" class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Ejército</span>
            <span
              v-if="project.status === 'completed'"
              class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700"
            >
              Completado
            </span>
          </div>
          <p class="text-gray-600 text-sm">{{ project.description || 'Sin descripción' }}</p>
          <p class="text-gray-400 text-xs mt-1">{{ formatDate(project.createdAt) }}</p>
        </button>

        <div class="sm:border-l sm:pl-5" aria-label="Unidades y miniaturas">
          <template v-if="project.type === 'army'">
            <p class="font-semibold text-gray-900">{{ project.unitCount }} {{ project.unitCount === 1 ? 'unidad' : 'unidades' }}</p>
            <p class="text-xs text-gray-500">{{ project.miniatureCount }} {{ project.miniatureCount === 1 ? 'miniatura' : 'miniaturas' }}</p>
          </template>
          <span v-else class="text-sm text-gray-400">—</span>
        </div>

        <div class="sm:border-l sm:pl-5">
          <span
            class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full"
            :class="project.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
          >
            {{ project.status === 'completed' ? 'Completado' : 'Activo' }}
          </span>
        </div>

        <div class="col-span-2 sm:col-span-1 flex items-center gap-2 justify-end" @click.stop>
          <button
            @click="startEdit"
            class="text-sm text-blue-700 hover:text-blue-900 underline-offset-2 hover:underline transition-colors"
            :aria-label="`Editar ejército ${project.name}`"
          >
            Editar
          </button>
          <button
            @click="deletingProject = true"
            class="text-sm text-red-700 hover:text-red-900 underline-offset-2 hover:underline transition-colors"
            :aria-label="`Eliminar ejército ${project.name}`"
          >
            Eliminar
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

  </article>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import { useBlockingAction } from '@/composables/useBlockingAction'
import { useToast } from '@/composables/useToast'
import { formatDate } from '@/utils/format'
import { projectApi } from '@/api/projectApi'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'
import type { Project } from '@/types/models'

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
    router.push(`/armies/${props.project.id}`)
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

  await run('Guardando ejército...', async () => {
    try {
      const data = await projectApi.update(
        props.project.id,
        editForm.value.name.trim(),
        editForm.value.description.trim(),
      )

      projectStore.updateProject({
        id:          data.id,
        name:        data.name,
        description: data.description ?? '',
      })

      cancelEdit()
      toast.success(`Ejército "${data.name}" actualizado`)

    } catch (e: any) {
      editError.value = e.response?.data?.error ?? 'Error al actualizar'
    }
  })
}

async function handleDelete() {
  await run('Eliminando ejército...', async () => {
    try {
      await projectApi.remove(props.project.id)
      projectStore.removeProject(props.project.id)
      toast.success(`Ejército "${props.project.name}" eliminado`)
    } catch {
      toast.error('Error al eliminar el ejército')
    }
  })
}
</script>
