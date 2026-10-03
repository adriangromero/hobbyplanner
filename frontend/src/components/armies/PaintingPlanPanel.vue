<template>
  <section class="border-t border-gray-200 pt-5" :aria-labelledby="`painting-plan-heading-${unit.id}`">
    <header class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 :id="`painting-plan-heading-${unit.id}`" class="font-semibold text-gray-900">Plan de pintado</h2>
        <p class="mt-0.5 text-sm text-gray-600">Estimación, tiempo registrado y sesiones de esta unidad</p>
      </div>
      <button v-if="unit.paintingPlan" type="button" class="rounded-lg border px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50" :aria-expanded="showSessions" @click="toggleSessions">
        {{ showSessions ? 'Ocultar historial' : 'Historial' }} · {{ unit.paintingPlan.sessionCount }} {{ unit.paintingPlan.sessionCount === 1 ? 'sesión' : 'sesiones' }}
      </button>
    </header>

    <form v-if="!unit.paintingPlan" class="mt-4 grid gap-3 rounded-xl border bg-gray-50 p-4 sm:grid-cols-[minmax(12rem,1fr)_auto] sm:items-end" @submit.prevent="createPlan">
      <div>
        <label :for="`estimate-${unit.id}`" class="mb-1 block text-sm font-medium text-gray-700">Horas estimadas para pintar la unidad</label>
        <input :id="`estimate-${unit.id}`" v-model.number="estimatedHours" type="number" min="0.5" step="0.25" required class="w-full rounded-lg border bg-white px-3 py-2" placeholder="Ej. 14" />
      </div>
      <button type="submit" :disabled="saving" class="rounded-lg bg-blue-700 px-4 py-2 font-medium text-white hover:bg-blue-800 disabled:opacity-50">{{ saving ? 'Guardando…' : 'Crear plan de pintado' }}</button>
    </form>

    <div v-else class="mt-4 space-y-4">
      <div class="rounded-xl border bg-gray-50 p-4">
        <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Estimadas</dt>
            <dd class="mt-1 flex flex-wrap items-center gap-2 font-semibold text-gray-900">
              {{ formatHours(unit.paintingPlan.estimatedHours) }}
              <button type="button" class="text-xs font-medium text-blue-700 hover:underline" @click="openEstimateEditor">Cambiar</button>
            </dd>
          </div>
          <div><dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Trabajadas</dt><dd class="mt-1 font-semibold text-gray-900">{{ formatHours(unit.paintingPlan.workedHours) }}</dd></div>
          <div><dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Restantes</dt><dd class="mt-1 font-semibold text-gray-900">{{ formatHours(unit.paintingPlan.remainingHours) }}</dd></div>
          <div><dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Sesiones</dt><dd class="mt-1 font-semibold text-gray-900">{{ unit.paintingPlan.sessionCount }}</dd></div>
        </dl>
        <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-200" role="progressbar" :aria-valuenow="progressPercent" aria-valuemin="0" aria-valuemax="100" :aria-label="`Tiempo estimado trabajado: ${progressPercent}%`">
          <div class="h-full rounded-full bg-emerald-700 transition-all" :style="{ width: `${progressPercent}%` }" />
        </div>
        <div v-if="editingEstimate" class="mt-4 rounded-lg border bg-white p-3">
          <form class="flex flex-wrap items-end gap-3" @submit.prevent="saveEstimate">
            <div class="min-w-40 flex-1">
              <label :for="`edit-estimate-${unit.id}`" class="mb-1 block text-sm font-medium text-gray-700">Nueva estimación (horas)</label>
              <input :id="`edit-estimate-${unit.id}`" v-model.number="estimatedHours" type="number" min="0.25" step="0.25" required class="w-full rounded-lg border px-3 py-2" />
            </div>
            <button type="submit" :disabled="saving" class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-50">Guardar</button>
            <button type="button" class="rounded-lg border px-3 py-2 text-sm" @click="editingEstimate = false">Cancelar</button>
          </form>
        </div>
        <div v-if="unit.paintingPlan.estimation?.activeDays" class="mt-4 rounded-lg border bg-white px-3 py-2 text-sm text-gray-700">
          <p v-if="unit.paintingPlan.estimation.estimatedCompletionDate && unit.paintingPlan.remainingHours > 0">
            Según el ritmo actual, terminarías esta unidad el <strong>{{ formatEstimatedDate(unit.paintingPlan.estimation.estimatedCompletionDate) }}</strong>.
          </p>
          <p v-else-if="unit.paintingPlan.remainingHours <= 0" class="font-medium text-emerald-800">Estimación de horas alcanzada.</p>
          <p class="mt-1 text-xs text-gray-600">{{ unit.paintingPlan.estimation.velocityPerActiveDay.toFixed(1) }} h por día de pintado · {{ unit.paintingPlan.estimation.frequencyDaysPerWeek.toFixed(1) }} días por semana · cálculo desde la primera sesión</p>
        </div>
        <p v-else class="mt-3 text-sm text-gray-600">La fecha estimada aparecerá al registrar sesiones.</p>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-white p-4">
        <div>
          <p v-if="isActive" class="font-mono text-sm font-semibold text-red-700">Sesión en curso · {{ timer.elapsedFormatted }}</p>
          <p v-else-if="timer.isRunning" class="text-sm text-gray-700">Temporizador activo en {{ timer.activeItemName }}</p>
          <p v-else class="text-sm text-gray-600">Usa el temporizador o anota a mano el tiempo dedicado.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button v-if="isActive" type="button" :disabled="saving" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50" @click="stopTimer">{{ saving ? 'Guardando…' : 'Finalizar sesión' }}</button>
          <button v-else type="button" :disabled="timer.isRunning || saving" :title="timer.isRunning ? `Finaliza la sesión de ${timer.activeItemName} antes de iniciar otra` : 'Iniciar sesión de pintado'" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800 disabled:opacity-50" @click="startTimer">Iniciar temporizador</button>
          <button type="button" :disabled="saving || timer.isRunning" :title="timer.isRunning ? 'Finaliza la sesión activa antes de anotar tiempo manual' : undefined" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 disabled:opacity-50" @click="manualFormOpen = !manualFormOpen">{{ manualFormOpen ? 'Cancelar horas manuales' : 'Añadir horas manualmente' }}</button>
        </div>
      </div>

      <form v-if="manualFormOpen" class="grid gap-3 rounded-xl border border-blue-200 bg-blue-50/60 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end" @submit.prevent="recordManualSession">
        <div>
          <label :for="`manual-plan-hours-${unit.id}`" class="mb-1 block text-sm font-medium text-gray-800">Horas</label>
          <input :id="`manual-plan-hours-${unit.id}`" v-model.number="manualHours" type="number" min="0" max="999" step="1" required class="w-full rounded-lg border bg-white px-3 py-2" />
        </div>
        <div>
          <label :for="`manual-plan-minutes-${unit.id}`" class="mb-1 block text-sm font-medium text-gray-800">Minutos</label>
          <input :id="`manual-plan-minutes-${unit.id}`" v-model.number="manualMinutes" type="number" min="0" max="59" step="1" required class="w-full rounded-lg border bg-white px-3 py-2" />
        </div>
        <button type="submit" :disabled="saving || timer.isRunning" class="rounded-lg bg-blue-700 px-4 py-2 font-medium text-white hover:bg-blue-800 disabled:opacity-50">{{ saving ? 'Guardando…' : 'Guardar sesión' }}</button>
        <p v-if="timer.isRunning" class="text-sm text-amber-900 sm:col-span-3">Finaliza primero la sesión activa para evitar contar el mismo tiempo dos veces.</p>
        <p v-else class="text-sm text-gray-700 sm:col-span-3">Registra tiempo trabajado sin cambiar cuántas miniaturas están pintadas.</p>
      </form>

      <div v-if="showSessions" class="rounded-xl border bg-white p-4" aria-live="polite">
        <div class="flex flex-wrap items-start justify-between gap-2">
          <div>
            <h3 class="font-semibold text-gray-900">Historial de sesiones</h3>
            <p class="text-xs text-gray-600">Puedes corregir la duración de cualquier sesión guardada.</p>
          </div>
          <p v-if="sessions.length" class="text-sm font-medium text-gray-700">{{ sessions.length }} {{ sessions.length === 1 ? 'sesión' : 'sesiones' }}</p>
        </div>
        <p v-if="loadingSessions" class="py-3 text-sm text-gray-600" role="status">Cargando sesiones…</p>
        <ul v-else-if="sessions.length" class="mt-3 divide-y divide-gray-200">
          <li v-for="session in sortedSessions" :key="session.id" class="py-3">
            <form v-if="editingSessionId === session.id" class="grid gap-3 rounded-lg bg-gray-50 p-3 sm:grid-cols-[minmax(12rem,1fr)_8rem_8rem_auto_auto] sm:items-end" @submit.prevent="saveSessionDuration(session.id)">
              <p class="text-sm text-gray-700">{{ formatDate(session.workedAt) }}</p>
              <div>
                <label :for="`session-hours-${session.id}`" class="mb-1 block text-xs font-medium text-gray-700">Horas</label>
                <input :id="`session-hours-${session.id}`" v-model.number="editHours" type="number" min="0" max="999" step="1" required class="w-full rounded-lg border bg-white px-2 py-2" />
              </div>
              <div>
                <label :for="`session-minutes-${session.id}`" class="mb-1 block text-xs font-medium text-gray-700">Minutos</label>
                <input :id="`session-minutes-${session.id}`" v-model.number="editMinutes" type="number" min="0" max="59" step="1" required class="w-full rounded-lg border bg-white px-2 py-2" />
              </div>
              <button type="submit" :disabled="saving" class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-50">Guardar</button>
              <button type="button" class="rounded-lg border px-3 py-2 text-sm" @click="editingSessionId = null">Cancelar</button>
            </form>
            <div v-else class="flex flex-wrap items-center justify-between gap-3">
              <time class="text-sm text-gray-700" :datetime="session.workedAt">{{ formatDate(session.workedAt) }}</time>
              <div class="flex items-center gap-3">
                <span class="font-semibold tabular-nums text-gray-900">{{ formatHours(session.durationHours) }}</span>
                <button type="button" class="rounded-lg border px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-50" :disabled="saving" @click="openSessionEditor(session)">Editar duración</button>
              </div>
            </div>
          </li>
        </ul>
        <p v-else class="mt-3 text-sm text-gray-600">Aún no hay sesiones. Puedes iniciar el temporizador o añadir horas manualmente.</p>
      </div>
    </div>
    <p v-if="error" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800" role="alert">{{ error }}</p>
  </section>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import { useTimerStore } from '@/stores/timerStore'
import { formatHours } from '@/utils/format'
import type { PaintingSession, ProjectUnit } from '@/types/models'

const props = defineProps<{ unit: ProjectUnit }>()
const projectStore = useProjectStore()
const timer = useTimerStore()
const estimatedHours = ref<number | null>(null)
const manualHours = ref(0)
const manualMinutes = ref(30)
const editHours = ref(0)
const editMinutes = ref(0)
const saving = ref(false)
const showSessions = ref(false)
const manualFormOpen = ref(false)
const editingEstimate = ref(false)
const editingSessionId = ref<string | null>(null)
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
    const plan = await projectStore.createPaintingPlan(props.unit.id, estimatedHours.value)
    showSessions.value = true
    await loadSessions(plan.id)
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo crear el plan de pintado.'
  } finally {
    saving.value = false
  }
}

function openEstimateEditor() {
  estimatedHours.value = props.unit.paintingPlan?.estimatedHours ?? null
  editingEstimate.value = true
}

async function saveEstimate() {
  const plan = props.unit.paintingPlan
  if (!plan || estimatedHours.value === null || !Number.isFinite(estimatedHours.value) || estimatedHours.value <= 0) {
    error.value = 'Indica una estimación de horas mayor que cero.'
    return
  }
  saving.value = true
  error.value = ''
  try {
    await projectStore.updatePaintingPlanEstimate(plan.id, estimatedHours.value)
    editingEstimate.value = false
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo actualizar la estimación.'
  } finally {
    saving.value = false
  }
}

async function toggleSessions() {
  showSessions.value = !showSessions.value
  const planId = props.unit.paintingPlan?.id
  if (showSessions.value && planId) await loadSessions(planId)
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
  const plan = props.unit.paintingPlan
  if (!plan) return
  error.value = ''
  try {
    timer.startPaintingPlan(plan.id, props.unit.name)
  } catch (e: any) {
    error.value = e.message ?? 'No se pudo iniciar el temporizador.'
  }
}

async function stopTimer() {
  saving.value = true
  error.value = ''
  try {
    const result = await timer.stop()
    if (!result || result.kind !== 'painting-plan') {
      error.value = 'No se pudo guardar la sesión de pintado.'
      return
    }
    projectStore.applyPaintingSession(result.session)
    await projectStore.refreshPaintingEstimates()
    if (showSessions.value) await loadSessions(result.planId)
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo finalizar la sesión.'
  } finally {
    saving.value = false
  }
}

async function recordManualSession() {
  const plan = props.unit.paintingPlan
  if (!plan) return
  if (timer.isRunning) {
    error.value = 'Finaliza la sesión activa antes de anotar tiempo manual.'
    return
  }
  const durationSeconds = durationFromFields(manualHours.value, manualMinutes.value)
  if (durationSeconds === null) {
    error.value = 'Indica un tiempo válido de al menos un minuto.'
    return
  }
  saving.value = true
  error.value = ''
  try {
    await projectStore.recordPaintingSession(plan.id, durationSeconds)
    manualFormOpen.value = false
    manualHours.value = 0
    manualMinutes.value = 30
    showSessions.value = true
    await loadSessions(plan.id)
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo guardar la sesión manual.'
  } finally {
    saving.value = false
  }
}

function openSessionEditor(session: PaintingSession) {
  editingSessionId.value = session.id
  editHours.value = Math.floor(session.durationSeconds / 3600)
  editMinutes.value = Math.round((session.durationSeconds % 3600) / 60)
  if (editMinutes.value === 60) {
    editHours.value += 1
    editMinutes.value = 0
  }
  error.value = ''
}

async function saveSessionDuration(sessionId: string) {
  const durationSeconds = durationFromFields(editHours.value, editMinutes.value)
  if (durationSeconds === null) {
    error.value = 'Indica un tiempo válido de al menos un minuto.'
    return
  }
  saving.value = true
  error.value = ''
  try {
    await projectStore.updatePaintingSession(sessionId, durationSeconds)
    editingSessionId.value = null
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo actualizar la sesión.'
  } finally {
    saving.value = false
  }
}

function durationFromFields(hours: number, minutes: number): number | null {
  if (!Number.isInteger(hours) || hours < 0 || hours > 999 || !Number.isInteger(minutes) || minutes < 0 || minutes > 59) return null
  const seconds = hours * 3600 + minutes * 60
  return seconds > 0 ? seconds : null
}

function formatDate(value: string): string {
  return new Intl.DateTimeFormat('es-ES', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
}

function formatEstimatedDate(value: string): string {
  return new Intl.DateTimeFormat('es-ES', { dateStyle: 'medium' }).format(new Date(`${value}T00:00:00`))
}
</script>
