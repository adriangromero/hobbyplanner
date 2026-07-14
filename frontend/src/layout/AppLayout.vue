<template>
  <div v-if="showChrome" class="min-h-screen bg-gray-100">
    <NavBar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <RouterView />
    </main>
  </div>

  <RouterView v-else />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import NavBar from '@/components/layout/NavBar.vue'

const route = useRoute()

// Routes that don't require auth (e.g. Login) render without the app chrome
const showChrome = computed(() => route.meta.requiresAuth !== false)
</script>
