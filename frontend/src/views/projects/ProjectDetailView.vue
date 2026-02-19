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

      <h2 class="text-xl font-semibold mb-3">Items</h2>

      <ItemsTable :items="items" />
    </div>

    <div v-else class="text-center text-gray-500">
      Project not found
    </div>

  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import ItemsTable from '@/components/items/ItemsTable.vue'

const store = useProjectStore()
const route = useRoute()

const project = computed(() => store.currentProject)
const items = computed(() => store.items)

onMounted(() => {
  store.loadProject(route.params.id)
})
</script>
