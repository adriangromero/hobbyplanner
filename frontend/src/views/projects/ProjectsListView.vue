<template>
  <div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Proyectos</h1>

    <div class="space-y-3">
      <button
        @click="showCreateProjectModal = true"
        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
      >
        Crear proyecto
      </button>

      <ProjectCard
        v-for="p in store.projects"
        :key="p.id"
        :project="p"
      />
    </div>

    <CreateProjectModal
      v-if="showCreateProjectModal"
      @close="showCreateProjectModal = false"
    />

  </div>

  <BlockingOverlay :active="store.loading" message="Cargando..." />
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import CreateProjectModal from '@/components/projects/CreateProjectModal.vue'
import ProjectCard from '@/components/projects/ProjectCard.vue'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'

const store = useProjectStore()

const showCreateProjectModal = ref(false)

onMounted(() => store.loadProjects())
</script>
