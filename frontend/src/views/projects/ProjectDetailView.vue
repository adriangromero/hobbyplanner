<template>
  <div class="max-w-3xl mx-auto p-6">

    <div v-if="store.loading" class="text-gray-500">Loading...</div>

    <div v-else-if="project">
      <router-link 
        to="/projects"
        class="text-blue-600 hover:underline text-sm"
      >
        ← Back to Projects
      </router-link>

      <h1 class="text-3xl font-bold mt-4">{{ project.name }}</h1>
      <p class="text-gray-600 mb-6">{{ project.description }}</p>

      <ProjectEstimationCard class="mb-6" />

      <!-- Header Items + Botón -->
      <div class="flex justify-between items-center mb-3">
        <h2 class="text-xl font-semibold">Items</h2>
        <button
          @click="showStartWorkSessionModal = true"
          class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
        >
          ▶ Nueva Sesión
        </button>
      </div>

      <ItemsTable :items="items" />
    </div>

    <div v-else class="text-center text-gray-500">
      Project not found
    </div>

    <!-- Modal Nueva Sesión -->
    <StartWorkSessionModal
      v-if="showStartWorkSessionModal"
      :items="items"
      @close="showStartWorkSessionModal = false"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import ItemsTable from '@/components/items/ItemsTable.vue'
import ProjectEstimationCard from '@/components/projects/ProjectEstimationCard.vue'
import StartWorkSessionModal from '@/components/sessions/StartWorkSessionModal.vue'

const store = useProjectStore()
const route = useRoute()

const project = computed(() => store.currentProject)
const items = computed(() => store.items)
const showStartWorkSessionModal = ref(false)

onMounted(() => {
  store.loadProject(route.params.id)
})
</script>