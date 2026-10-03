<template>
  <section
    v-if="est && est.estimatedHours > 0"
    class="p-5 bg-white shadow-sm rounded-xl border border-gray-200 space-y-4 transition-shadow duration-300"
    :class="{ 'ring-2 ring-blue-400/60 shadow-blue-100 shadow-md': highlighting }"
    aria-labelledby="army-estimation-heading"
  >
    <div class="flex items-center justify-between gap-3">
      <h2 id="army-estimation-heading" class="text-lg font-semibold text-gray-800">Estimación del ejército</h2>
      <span
        v-if="progressPercent >= 100"
        class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700"
      >
        Horas previstas alcanzadas
      </span>
    </div>

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
      </p>
    </div>

    <!-- Métricas de ritmo -->
    <div v-if="est.activeDays > 0" class="grid grid-cols-2 gap-3">
      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-500">Ritmo diario</p>
        <p class="text-lg font-bold text-gray-800">
          {{ est.velocityPerActiveDay.toFixed(1) }}<span class="text-sm font-normal text-gray-500">h/día</span>
        </p>
      </div>

      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-500">Frecuencia de pintado</p>
        <p class="text-lg font-bold text-gray-800">
          {{ est.frequencyDaysPerWeek.toFixed(1) }}<span class="text-sm font-normal text-gray-500">días/sem</span>
        </p>
        <p class="text-xs text-gray-500">Media desde la primera sesión</p>
      </div>

      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-500">Días trabajados</p>
        <p class="text-lg font-bold text-gray-800">{{ est.activeDays }}</p>
      </div>

      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-xs text-gray-500">Días de pintado restantes</p>
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

    <!-- Sin sesiones aún -->
    <p v-if="est.activeDays === 0" class="text-sm text-gray-600 text-center py-2">
      Registra sesiones de pintado para calcular el ritmo y la fecha estimada.
    </p>
  </section>

  <section v-else-if="est" class="rounded-xl border border-dashed border-gray-300 bg-white p-5" aria-labelledby="army-estimation-heading">
    <h2 id="army-estimation-heading" class="text-lg font-semibold text-gray-800">Estimación del ejército</h2>
    <p class="mt-1 text-sm text-gray-600">Crea un plan de pintado en una unidad para ver horas estimadas, progreso y fecha de finalización.</p>
  </section>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import { formatHours } from '@/utils/format'

const store = useProjectStore()
const est   = computed(() => store.estimation)

const highlighting    = ref(false)
let   highlightTimer: ReturnType<typeof setTimeout> | undefined

watch(est, (newVal, oldVal) => {
  if (!oldVal || !newVal) return
  highlighting.value = true
  clearTimeout(highlightTimer)
  highlightTimer = setTimeout(() => (highlighting.value = false), 800)
}, { deep: true })

const progressPercent = computed(() => {
  if (!est.value || est.value.estimatedHours === 0) return 0
  return Math.min(100, Math.max(0,
    ((est.value.estimatedHours - est.value.remainingHours) / est.value.estimatedHours) * 100
  ))
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
