<template>
  <main class="mx-auto max-w-6xl space-y-5">
    <template v-if="project?.type === 'army'">
      <header>
        <router-link :to="{ name: 'ArmyDetail', params: { id: project.id } }" class="text-sm text-blue-700 hover:underline">← Volver al inventario de {{ project.name }}</router-link>
        <h1 class="mt-3 text-3xl font-bold">Estimación del ejército</h1>
        <p class="mt-1 text-gray-600">{{ project.name }} · ritmo, sesiones y fecha estimada</p>
      </header>
      <ArmyEstimationCard />
      <section class="rounded-xl border bg-white p-5" aria-labelledby="unit-estimates-heading">
        <h2 id="unit-estimates-heading" class="text-lg font-semibold">Estimación por unidad</h2>
        <p class="mt-1 text-sm text-gray-600">Cada fecha se calcula con las sesiones registradas en el plan de esa unidad.</p>
        <div v-if="units.length" class="mt-4 grid gap-3 md:grid-cols-2">
          <article v-for="unit in units" :key="unit.id" class="rounded-lg border bg-gray-50 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="font-semibold text-gray-900">{{ unit.name }}</h3>
                <p class="text-sm text-gray-600">{{ categoryLabel(unit.category) }}</p>
              </div>
              <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs text-gray-700">{{ unit.paintingPlan?.sessionCount ?? 0 }} sesiones</span>
            </div>
            <template v-if="unit.paintingPlan">
              <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
                <div><p class="text-xs text-gray-500">Estimadas</p><p class="font-medium">{{ formatHours(unit.paintingPlan.estimatedHours) }}</p></div>
                <div><p class="text-xs text-gray-500">Trabajadas</p><p class="font-medium">{{ formatHours(unit.paintingPlan.workedHours) }}</p></div>
                <div><p class="text-xs text-gray-500">Restantes</p><p class="font-medium">{{ formatHours(unit.paintingPlan.remainingHours) }}</p></div>
              </div>
              <p v-if="unit.paintingPlan.estimation?.estimatedCompletionDate && unit.paintingPlan.remainingHours > 0" class="mt-3 text-sm text-blue-800">
                Finalización estimada: <strong>{{ formatDate(unit.paintingPlan.estimation.estimatedCompletionDate) }}</strong>
              </p>
              <p v-else-if="unit.paintingPlan.remainingHours <= 0" class="mt-3 text-sm font-medium text-green-800">Horas estimadas alcanzadas.</p>
              <p v-else class="mt-3 text-sm text-gray-600">Registra sesiones para calcular la fecha.</p>
            </template>
            <p v-else class="mt-3 text-sm text-gray-600">Esta unidad todavía no tiene un plan de pintado.</p>
          </article>
        </div>
        <p v-else class="mt-4 rounded-lg border border-dashed p-5 text-sm text-gray-600">Añade unidades y crea planes de pintado para ver las estimaciones aquí.</p>
      </section>
    </template>
    <p v-else-if="!store.loading" class="rounded-xl border bg-white p-6 text-center text-gray-600">No se encontró este ejército.</p>
    <BlockingOverlay :active="store.loading" message="Cargando estimaciones..." />
  </main>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import { useThemeStore } from '@/stores/themeStore'
import ArmyEstimationCard from '@/components/armies/ArmyEstimationCard.vue'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'
import { UNIT_CATEGORIES } from '@/types/models'
import { formatHours } from '@/utils/format'

const route = useRoute()
const store = useProjectStore()
const themeStore = useThemeStore()
const project = computed(() => store.currentProject)
const units = computed(() => store.units)
const projectId = computed(() => typeof route.params.id === 'string' ? route.params.id : null)

watch(projectId, (id) => {
  if (id) void store.loadProject(id)
}, { immediate: true })
watch([projectId, project], ([id, value]) => {
  themeStore.activateArmy(id && value?.id === id && value.type === 'army' ? id : null)
}, { immediate: true })
onBeforeUnmount(() => themeStore.activateArmy(null))

function categoryLabel(category: string): string {
  return UNIT_CATEGORIES.find((entry) => entry.value === category)?.label ?? category
}
function formatDate(value: string): string {
  return new Intl.DateTimeFormat('es-ES', { dateStyle: 'medium' }).format(new Date(value + 'T00:00:00'))
}
</script>
