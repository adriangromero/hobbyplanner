<template>
  <div v-if="est" class="p-4 bg-white shadow rounded space-y-1 border border-gray-200">
    <h2 class="text-xl font-semibold mb-2">Project Estimation</h2>

    <p><strong>Start date:</strong> {{ est.startDate ?? '—' }}</p>
    <p><strong>Worked:</strong> {{ est.workedHours.toFixed(1) ?? '—' }}h</p>
    <p><strong>Remaining:</strong> {{ est.remainingHours.toFixed(1) ?? '—' }}h</p>
    <p><strong>Velocity:</strong> {{ est.velocityPerDay.toFixed(2) ?? '—' }}h/day</p>
    <p><strong>Days remaining:</strong> {{ est.daysRemaining ?? '—' }}</p>
    <p><strong>Estimated completion:</strong> {{ est.estimatedCompletionDate ?? '—' }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useProjectStore } from '@/stores/projectStore'

const store = useProjectStore()
const est = computed(() => store.estimation)
</script>
