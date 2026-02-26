<template>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Projects</h1>

    <div v-if="store.loading" class="text-gray-500">Loading...</div>

    <div v-else class="space-y-3">
      <button
        @click="showCreateProjectModal = true"
        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
      >
        Create Project
      </button>

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

    <CreateProjectModal
      v-if="showCreateProjectModal"
      @close="showCreateProjectModal = false"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import CreateProjectModal from '@/components/projects/CreateProjectModal.vue'

const store  = useProjectStore()
const router = useRouter()

const showCreateProjectModal = ref(false)

onMounted(() => store.loadProjects())

function go(id: string) {
  router.push(`/projects/${id}`)
}
</script>