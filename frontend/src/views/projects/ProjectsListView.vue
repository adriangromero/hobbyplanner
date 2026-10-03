<template>
  <div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-2">Proyectos</h1>
    <p class="text-gray-600 mb-6">Cada proyecto puede organizar un trabajo creativo o un ejército con su inventario y progreso de pintado.</p>

    <div class="flex flex-wrap gap-2 mb-5" aria-label="Filtrar proyectos">
      <button v-for="option in filters" :key="option.value" type="button" class="px-3 py-1.5 rounded-full border text-sm"
        :class="filter === option.value ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-gray-700 hover:bg-gray-50'"
        @click="filter = option.value">
        {{ option.label }}
      </button>
    </div>

    <div class="space-y-3">
      <button
        @click="showCreateProjectModal = true"
        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
      >
        {{ filter === 'army' ? 'Crear ejército' : 'Crear proyecto' }}
      </button>

      <ProjectCard
        v-for="p in filteredProjects"
        :key="p.id"
        :project="p"
      />
    </div>

    <CreateProjectModal
      v-if="showCreateProjectModal"
      :initial-type="filter === 'army' ? 'army' : 'general'"
      @close="showCreateProjectModal = false"
    />

  </div>

  <BlockingOverlay :active="store.loading" message="Cargando..." />
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import CreateProjectModal from '@/components/projects/CreateProjectModal.vue'
import ProjectCard from '@/components/projects/ProjectCard.vue'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'

const store = useProjectStore()
const route = useRoute()

const showCreateProjectModal = ref(false)
const filter = ref<'all' | 'general' | 'army'>('all')
const filters = [
  { value: 'all', label: 'Todos' },
  { value: 'general', label: 'Generales' },
  { value: 'army', label: 'Ejércitos' },
] as const
const filteredProjects = computed(() => filter.value === 'all'
  ? store.projects
  : store.projects.filter((project) => project.type === filter.value))

function syncFilterFromRoute() {
  filter.value = route.query.type === 'army' || route.query.type === 'general'
    ? route.query.type
    : 'all'
}

onMounted(() => {
  syncFilterFromRoute()
  void store.loadProjects()
})

watch(() => route.query.type, syncFilterFromRoute)
</script>
