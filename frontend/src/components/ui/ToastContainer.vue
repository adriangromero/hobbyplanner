<template>
  <Teleport to="body">
    <div class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2">
      <TransitionGroup
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-x-full opacity-0"
        enter-to-class="translate-x-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-x-0 opacity-100"
        leave-to-class="translate-x-full opacity-0"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium min-w-64"
          :class="{
            'bg-green-600 text-white': toast.type === 'success',
            'bg-red-600 text-white':   toast.type === 'error',
            'bg-blue-600 text-white':  toast.type === 'info',
          }"
        >
          <span>{{ icon(toast.type) }}</span>
          <span>{{ toast.message }}</span>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast'

const { toasts } = useToast()

function icon(type: string): string {
  if (type === 'success') return '✅'
  if (type === 'error')   return '❌'
  return 'ℹ️'
}
</script>