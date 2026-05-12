<template>
  <div class="max-w-3xl mx-auto">

    <div v-if="store.loading" class="text-gray-500">Cargando...</div>

    <div v-else-if="project">
      <router-link
        to="/projects"
        class="text-blue-600 hover:underline text-sm"
      >
        ← Volver a Proyectos
      </router-link>

      <div class="flex items-center gap-3 mt-4 mb-1">
        <h1 class="text-3xl font-bold">{{ project.name }}</h1>
        <span
          v-if="project.status === 'completed'"
          class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700"
        >
          Completado
        </span>
      </div>
      <p class="text-gray-600 mb-4">{{ project.description }}</p>

      <!-- Botón completar/reactivar proyecto -->
      <button
        @click="handleToggleProjectStatus"
        :disabled="togglingStatus"
        class="text-sm font-medium px-4 py-1.5 rounded-lg transition mb-6 disabled:opacity-50"
        :class="project.status === 'completed'
          ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          : 'bg-green-600 text-white hover:bg-green-700'"
      >
        {{ project.status === 'completed' ? 'Reactivar proyecto' : 'Marcar como completado' }}
      </button>

      <ProjectEstimationCard class="mb-6" />

      <ItemsTable :items="items" />
    </div>

    <div v-else class="text-center text-gray-500">
      Proyecto no encontrado
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import { useToast } from '@/composables/useToast'
import { projectApi } from '@/api/projectApi'
import ItemsTable from '@/components/items/ItemsTable.vue'
import ProjectEstimationCard from '@/components/projects/ProjectEstimationCard.vue'

const store = useProjectStore()
const route = useRoute()
const toast = useToast()

const togglingStatus = ref(false)

const project = computed(() => store.currentProject)
const items   = computed(() => store.items)

onMounted(() => {
  store.loadProject(route.params.id as string)
})

async function handleToggleProjectStatus() {
  if (!project.value) return
  togglingStatus.value = true

  try {
    const data = await projectApi.toggleStatus(project.value.id)
    store.toggleProjectStatus(data.status)
    store.refreshEstimation()
    toast.success(
      data.status === 'completed'
        ? `Proyecto "${data.name}" completado`
        : `Proyecto "${data.name}" reactivado`
    )
  } catch {
    toast.error('Error al cambiar el estado del proyecto')
  } finally {
    togglingStatus.value = false
  }
}
</script>
