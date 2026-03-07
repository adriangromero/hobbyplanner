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

      <h1 class="text-3xl font-bold mt-4">{{ project.name }}</h1>
      <p class="text-gray-600 mb-6">{{ project.description }}</p>

      <ProjectEstimationCard class="mb-6" />

      <ItemsTable :items="items" />
    </div>

    <div v-else class="text-center text-gray-500">
      Proyecto no encontrado
    </div>

  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import ItemsTable from '@/components/items/ItemsTable.vue'
import ProjectEstimationCard from '@/components/projects/ProjectEstimationCard.vue'

const store = useProjectStore()
const route = useRoute()

const project = computed(() => store.currentProject)
const items   = computed(() => store.items)

onMounted(() => {
  store.loadProject(route.params.id as string)
})
</script>