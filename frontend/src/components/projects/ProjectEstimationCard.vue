<template>
  <div v-if="est" class="p-5 bg-white shadow-sm rounded-lg border border-gray-200 space-y-4">
    <h2 class="text-lg font-semibold text-gray-800">Estimación</h2>

    <!-- Progreso horas -->
    <div>
      <div class="flex justify-between text-sm text-gray-600 mb-1">
        <span>{{ formatHours(est.workedHours) }} trabajadas</span>
        <span>{{ formatHours(est.estimatedHours) }} estimadas</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div
          class="h-2.5 rounded-full transition-all"
          :class="progressPercent >= 100 ? 'bg-green-500' : 'bg-blue-500'"
          :style="{ width: Math.min(progressPercent, 100) + '%' }"
        />
      </div>
      <p class="text-xs text-gray-400 mt-1">
        {{ formatHours(est.remainingHours) }} restantes
        <span v-if="progressPercent >= 100" class="text-green-600 font-medium ml-1">
          — Completado
        </span>
      </p>
    </div>

    <!-- Métricas de ritmo -->
    <div v-if="est.activeDays > 0" class="grid grid-cols-2 gap-3">
      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-500">Ritmo por sesión</p>
        <p class="text-lg font-bold text-gray-800">
          {{ est.velocityPerActiveDay.toFixed(1) }}<span class="text-sm font-normal text-gray-500">h/día</span>
        </p>
      </div>

      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-500">Frecuencia</p>
        <p class="text-lg font-bold text-gray-800">
          {{ est.frequencyDaysPerWeek.toFixed(1) }}<span class="text-sm font-normal text-gray-500">días/sem</span>
        </p>
      </div>

      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-500">Días trabajados</p>
        <p class="text-lg font-bold text-gray-800">{{ est.activeDays }}</p>
      </div>

      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-500">Sesiones restantes</p>
        <p class="text-lg font-bold text-gray-800">{{ est.activeDaysRemaining ?? '—' }}</p>
      </div>
    </div>

    <!-- Fecha estimada -->
    <div
      v-if="est.estimatedCompletionDate && est.remainingHours > 0"
      class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-center"
    >
      <p class="text-xs text-blue-500 mb-1">Fecha estimada de finalización</p>
      <p class="text-lg font-bold text-blue-700">{{ formatEstimatedDate(est.estimatedCompletionDate) }}</p>
      <p class="text-xs text-blue-400">~{{ est.daysRemaining }} días (~{{ weeksRemaining }} semanas)</p>
    </div>

    <!-- Sin datos aún -->
    <p v-if="est.activeDays === 0" class="text-sm text-gray-400 text-center py-2">
      Registra sesiones de trabajo para ver la estimación
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import { formatHours } from '@/utils/format'

const store = useProjectStore()
const est   = computed(() => store.estimation)

const progressPercent = computed(() => {
  if (!est.value || est.value.estimatedHours === 0) return 0
  return (est.value.workedHours / est.value.estimatedHours) * 100
})

const weeksRemaining = computed(() => {
  if (!est.value?.daysRemaining) return '—'
  return Math.ceil(est.value.daysRemaining / 7)
})

function formatEstimatedDate(dateStr: string): string {
  const date = new Date(dateStr + 'T00:00:00')
  return date.toLocaleDateString('es-ES', {
    day:   'numeric',
    month: 'long',
    year:  'numeric',
  })
}
</script>
