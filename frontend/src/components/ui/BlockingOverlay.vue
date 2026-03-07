<template>
  <!-- Fullscreen: Teleport al body, bloquea toda la pantalla -->
  <Teleport v-if="mode === 'fullscreen'" to="body">
    <div
      v-if="active"
      class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[9998]"
    >
      <div class="bg-white rounded-xl shadow-2xl px-10 py-8 flex flex-col items-center gap-4">
        <div class="w-10 h-10 border-4 border-gray-200 border-t-blue-600 rounded-full animate-spin" />
        <p v-if="message" class="text-gray-700 font-semibold text-lg">{{ message }}</p>
        <p v-if="detail" class="text-gray-400 text-sm">{{ detail }}</p>
      </div>
    </div>
  </Teleport>

  <!-- Local: overlay absoluto dentro del contenedor padre -->
  <div
    v-else-if="active"
    class="absolute inset-0 bg-white/60 flex items-center justify-center rounded-lg z-10"
  >
    <div class="flex flex-col items-center gap-2">
      <div class="w-8 h-8 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin" />
      <p v-if="message" class="text-gray-500 text-sm font-medium">{{ message }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  active:   boolean
  message?: string
  detail?:  string
  mode?:    'fullscreen' | 'local'
}>(), {
  mode: 'fullscreen',
})
</script>
