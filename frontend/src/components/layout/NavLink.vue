<template>
  <router-link
    :to="to"
    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
    active-class="text-blue-600 bg-blue-50"
  >
    <span v-if="icon" class="text-lg">{{ icon }}</span>
    <span class="font-medium">
      <slot />
    </span>
  </router-link>
</template>

<script setup lang="ts">
defineProps<{
  to: string
  icon?: string
}>()
</script>
