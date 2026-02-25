<template>
  <header class="bg-white border-b px-6 py-3 flex items-center justify-between">
    <h2 class="font-semibold text-gray-700">Hobby Planner</h2>
    <div class="flex items-center gap-4">
      <span class="text-sm text-gray-500">{{ auth.user?.name }}</span>
      <button
        @click="auth.logout()"
        class="text-sm text-red-600 hover:text-red-700 font-medium transition"
      >
        Cerrar sesión
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/authStore'

const auth = useAuthStore()
</script>