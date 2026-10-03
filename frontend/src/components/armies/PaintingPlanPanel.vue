<template>
  <section class="border-t border-gray-200 pt-5" :aria-labelledby="`painting-plan-heading-${unit.id}`">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 :id="`painting-plan-heading-${unit.id}`" class="font-semibold text-gray-900">Plan de pintado</h2>
        <p class="text-sm text-gray-600 mt-0.5">Estimación y sesiones para la unidad completa</p>
      </div>
      <button v-if="unit.paintingPlan && !showSessions" type="button" class="text-sm text-blue-700 hover:underline" @click="toggleSessions">
        Ver sesiones ({{ sessions.length }})
      </button>
    </div>

    <form v-if="!unit.paintingPlan" class="mt-3 flex flex-wrap items-end gap-3" @submit.prevent="createPlan">
      <div>
        <label :for="`estimate-${unit.id}`" class="block text-sm font-medium text-gray-700 mb-1">Horas estimadas</label>
        <input :id="`estimate-${unit.id}`" v-model.number="estimatedHours" type="number" min="0.5" step="0.5" required class="w-36 border rounded-lg px-3 py-2" />
      </div>
      <button type="submit" :disabled="saving" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50">{{ saving ? 'Creando…' : 'Crear plan' }}</button>
    </form>

    <div v-else class="mt-3 rounded-lg bg-gray-50 border p-4">
      <div class="grid gap-3 sm:grid-cols-3">
        <div><p class="text-xs uppercase tracking-wide text-gray-500">Estimación</p><p class="font-semibold text-gray-900">{{ formatHours(unit.paintingPlan.estimatedHours) }}</p></div>
        <div><p class="text-xs uppercase tracking-wide text-gray-500">Trabajadas</p><p class="font-semibold text-gray-900">{{ formatHours(unit.paintingPlan.workedHours) }}</p></div>
        <div><p class="text-xs uppercase tracking-wide text-gray-500">Restantes</p><p class="font-semibold text-gray-900">{{ formatHours(unit.paintingPlan.remainingHours) }}</p></div>
      </div>
      <div class="mt-3 h-2 rounded-full bg-gray-200 overflow-hidden" role="progressbar" :aria-valuenow="progressPercent" aria-valuemin="0" aria-valuemax="100" :aria-label="`Tiempo estimado trabajado: ${progressPercent}%`">
        <div class="h-full bg-green-600 transition-all" :style="{ width: `${progressPercent}%` }" />
      </div>
      <div v-if="unit.paintingPlan.estimation?.activeDays" class="mt-3 rounded-lg border bg-white px-3 py-2 text-sm text-gray-700">
        <p v-if="unit.paintingPlan.estimation.estimatedCompletionDate && unit.paintingPlan.remainingHours > 0">
          Para esta unidad, el ritmo actual apunta al <strong>{{ formatEstimatedDate(unit.paintingPlan.estimation.estimatedCompletionDate) }}</strong>.
        </p>
        <p class="text-xs text-gray-600 mt-1">
          {{ unit.paintingPlan.estimation.velocityPerActiveDay.toFixed(1) }} h por día de pintado ·
          {{ unit.paintingPlan.estimation.frequencyDaysPerWeek.toFixed(1) }} días por semana · media desde la primera sesión
        </p>
      </div>
      <p v-else class="mt-3 text-xs text-gray-600">La fecha estimada de esta unidad aparecerá tras registrar sesiones de pintado.</p>
      <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
        <p v-if="isActive" class="font-mono text-sm font-semibold text-red-700">Sesión en curso · {{ timer.elapsedFormatted }}</p>
        <p v-else-if="timer.isRunning" class="text-sm text-gray-600">Temporizador activo en {{ timer.activeItemName }}</p>
        <p v-else class="text-sm text-gray-600">Registra una sesión para actualizar las horas trabajadas.</p>
        <div class="flex items-center gap-2">
          <button v-if="isActive" type="button" :disabled="saving" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white disabled:opacity-50" @click="stopTimer">{{ saving ? 'Guardando…' : 'Finalizar sesión' }}</button>
          <button v-else type="button" :disabled="timer.isRunning || saving" :title="timer.isRunning ? `Finaliza la sesión de ${timer.activeItemName} antes de iniciar otra` : 'Iniciar sesión de pintado'" class="px-4 py-2 rounded-lg bg-green-700 hover:bg-green-800 text-white disabled:opacity-50" @click="startTimer">Iniciar sesión</button>
        </div>
      </div>
      <div v-if="showSessions" class="mt-4 border-t pt-3">
        <h3 class="font-medium text-gray-900">Historial de sesiones</h3>
        <p v-if="loadingSessions" class="text-sm text-gray-600 py-3" role="status">Cargando sesiones…</p>
        <ul v-else-if="sessions.length" class="mt-2 divide-y divide-gray-200">
          <li v-for="session in sortedSessions" :key="session.id" class="py-2 flex flex-wrap justify-between gap-2 text-sm">
            <time :datetime="session.workedAt">{{ formatDate(session.workedAt) }}</time>
            <span class="font-medium">{{ formatHours(session.durationHours) }}</span>
          </li>
        </ul>
        <p v-else class="text-sm text-gray-600 mt-2">Aún no hay sesiones guardadas.</p>
      </div>
    </div>
    <p v-if="error" class="text-sm text-red-700 mt-3" role="alert">{{ error }}</p>
  </section>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import { useTimerStore } from '@/stores/timerStore'
import { formatHours } from '@/utils/format'
import type { ProjectUnit } from '@/types/models'

const props = defineProps<{ unit: ProjectUnit }>()
const projectStore = useProjectStore()
const timer = useTimerStore()
const estimatedHours = ref<number | null>(null)
const saving = ref(false)
const showSessions = ref(false)
const loadingSessions = ref(false)
const error = ref('')
const sessions = computed(() => props.unit.paintingPlan ? projectStore.sessionsByPlan[props.unit.paintingPlan.id] ?? [] : [])
const isActive = computed(() => timer.isRunning && timer.activeTargetType === 'painting-plan' && timer.activePaintingPlanId === props.unit.paintingPlan?.id)
const progressPercent = computed(() => {
  const plan = props.unit.paintingPlan
  if (!plan || plan.estimatedHours <= 0) return 0
  return Math.max(0, Math.min(100, Math.round((plan.workedHours / plan.estimatedHours) * 100)))
})
const sortedSessions = computed(() => [...sessions.value].sort((a, b) => b.workedAt.localeCompare(a.workedAt)))

watch(() => props.unit.paintingPlan?.id, (planId) => {
  if (planId && showSessions.value) void loadSessions(planId)
}, { immediate: true })

async function createPlan() {
  if (estimatedHours.value === null || !Number.isFinite(estimatedHours.value) || estimatedHours.value <= 0) return
  saving.value = true
  error.value = ''
  try {
    await projectStore.createPaintingPlan(props.unit.id, estimatedHours.value)
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo crear el plan de pintado.'
  } finally {
    saving.value = false
  }
}

async function toggleSessions() {
  showSessions.value = !showSessions.value
  if (showSessions.value && props.unit.paintingPlan) await loadSessions(props.unit.paintingPlan.id)
}

async function loadSessions(planId: string) {
  loadingSessions.value = true
  error.value = ''
  try {
    await projectStore.loadPaintingSessions(planId)
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudieron cargar las sesiones.'
  } finally {
    loadingSessions.value = false
  }
}

function startTimer() {
  error.value = ''
  const plan = props.unit.paintingPlan
  if (!plan) return
  try {
    timer.startPaintingPlan(plan.id, props.unit.name)
  } catch (e: any) {
    error.value = e.message ?? 'No se pudo iniciar el temporizador.'
  }
}

async function stopTimer() {
  saving.value = true
  error.value = ''
  const result = await timer.stop()
  if (!result || result.kind !== 'painting-plan') {
    error.value = 'No se pudo guardar la sesión de pintado.'
  } else {
    projectStore.applyPaintingSession(result.session)
    await projectStore.refreshPaintingEstimates()
    if (showSessions.value) await loadSessions(result.planId)
  }
  saving.value = false
}

function formatDate(value: string): string {
  return new Intl.DateTimeFormat('es-ES', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
}

function formatEstimatedDate(value: string): string {
  return new Intl.DateTimeFormat('es-ES', { dateStyle: 'medium' }).format(new Date(`${value}T00:00:00`))
}
</script>
