<template>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Projects</h1>

    <div v-if="store.loading" class="text-gray-500">Loading...</div>

    <div v-else class="space-y-3">
      <div
        v-for="p in store.projects"
        :key="p.id"
        @click="go(p.id)"
        class="p-4 bg-white rounded-lg shadow-sm border hover:shadow-md cursor-pointer transition"
      >
        <h2 class="text-xl font-semibold">{{ p.name }}</h2>
        <p class="text-gray-600 text-sm">{{ p.description || 'No description' }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'

const store = useProjectStore()
const router = useRouter()

onMounted(() => store.loadProjects())

function go(id) {
  router.push(`/projects/${id}`)
}
</script>
