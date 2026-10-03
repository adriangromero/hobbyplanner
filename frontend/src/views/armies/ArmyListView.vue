<template>
  <div class="max-w-5xl mx-auto">
    <header class="flex flex-wrap items-end justify-between gap-4 mb-6">
      <div>
        <p class="text-sm font-medium text-gray-500 mb-1">Tu espacio de hobby</p>
        <h1 class="text-3xl font-bold mb-2">Ejércitos</h1>
        <p class="text-gray-600 max-w-2xl">Consulta el inventario, las unidades y el progreso de pintado de cada ejército.</p>
      </div>
      <button
        type="button"
        @click="showCreateArmyModal = true"
        class="flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-green-700"
      >
        <span aria-hidden="true" class="text-lg leading-none">+</span>
        Crear ejército
      </button>
    </header>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-label="Lista de ejércitos">
      <div class="hidden sm:grid grid-cols-[minmax(0,1fr)_160px_125px_auto] items-center gap-5 px-4 py-3 border-b bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
        <span>Ejército</span>
        <span class="border-l pl-5">Unidades</span>
        <span class="border-l pl-5">Estado</span>
        <span class="text-right">Acciones</span>
      </div>
      <p v-if="store.error" class="m-4 rounded-lg bg-red-50 p-3 text-sm text-red-800" role="alert">{{ store.error }}</p>
      <div v-if="armies.length" class="divide-y divide-gray-100">
      <ProjectCard
        v-for="p in armies"
        :key="p.id"
        :project="p"
      />
      </div>
      <div v-else-if="!store.loading && !store.error" class="px-6 py-12 text-center">
        <p class="font-semibold text-gray-800">Todavía no tienes ejércitos</p>
        <p class="text-sm text-gray-600 mt-1">Crea uno para empezar a registrar unidades y miniaturas.</p>
      </div>
    </section>

    <CreateArmyModal
      v-if="showCreateArmyModal"
      @close="showCreateArmyModal = false"
    />

  </div>

  <BlockingOverlay :active="store.loading" message="Cargando..." />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import CreateArmyModal from '@/components/armies/CreateArmyModal.vue'
import ProjectCard from '@/components/projects/ProjectCard.vue'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'

const store = useProjectStore()

const showCreateArmyModal = ref(false)
const armies = computed(() => store.projects.filter((project) => project.type === 'army'))

onMounted(() => {
  void store.loadProjects()
})
</script>
