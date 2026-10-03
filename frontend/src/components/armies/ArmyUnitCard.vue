<template>
  <article class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <button
      type="button"
      class="w-full flex items-center justify-between gap-4 p-4 text-left hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
      :aria-expanded="expanded"
      @click="expanded = !expanded"
    >
      <span class="min-w-0">
        <span class="block text-lg font-semibold text-gray-900 truncate">{{ unit.name }}</span>
        <span class="block text-sm text-gray-600 mt-0.5">
          {{ componentCountLabel }} · {{ paintedTotal }}/{{ totalCount }} pintados
        </span>
        <span v-if="unit.paintingPlan" class="mt-1 block text-xs text-gray-600">
          <span class="block">
            Estimación de esta unidad: {{ formatHours(unit.paintingPlan.estimatedHours) }} estimadas ·
            {{ formatHours(unit.paintingPlan.workedHours) }} trabajadas ·
            {{ formatHours(unit.paintingPlan.remainingHours) }} restantes
          </span>
          <span v-if="unit.paintingPlan.estimation?.estimatedCompletionDate && unit.paintingPlan.remainingHours > 0" class="block">
            Finalización estimada: {{ formatEstimatedDate(unit.paintingPlan.estimation.estimatedCompletionDate) }}
          </span>
          <span v-else-if="unit.paintingPlan.remainingHours === 0" class="block">
            Horas estimadas alcanzadas
          </span>
          <span v-else class="block">Fecha estimada disponible tras registrar sesiones</span>
        </span>
        <span v-else class="mt-1 block text-xs text-gray-500">Sin plan de pintado</span>
      </span>
      <span class="flex items-center gap-3 shrink-0">
        <span class="hidden sm:inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
          {{ totalCount ? Math.round((paintedTotal / totalCount) * 100) : 0 }}%
        </span>
        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': expanded }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.2 7.2a.75.75 0 011.06 0L10 10.94l3.74-3.74a.75.75 0 111.06 1.06l-4.27 4.27a.75.75 0 01-1.06 0L5.2 8.26a.75.75 0 010-1.06z" clip-rule="evenodd" />
        </svg>
      </span>
    </button>

    <div v-if="expanded" class="border-t border-gray-200 p-4 sm:p-5 space-y-6">
      <section :aria-labelledby="`components-heading-${unit.id}`">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
          <div>
            <h2 :id="`components-heading-${unit.id}`" class="font-semibold text-gray-900">Composición</h2>
            <p class="text-sm text-gray-600">Cada casilla representa una miniatura del recuento.</p>
          </div>
          <button v-if="!showAddForm" type="button" class="text-sm font-medium text-blue-700 hover:underline" @click="showAddForm = true">Añadir componente</button>
        </div>

        <form v-if="showAddForm" class="bg-gray-50 border rounded-lg p-3 mb-4" @submit.prevent="addComponent">
          <div class="grid gap-3 sm:grid-cols-[1fr_9rem_auto_auto] sm:items-end">
            <div>
              <label :for="`new-label-${unit.id}`" class="block text-sm font-medium text-gray-700 mb-1">Etiqueta</label>
              <input :id="`new-label-${unit.id}`" v-model="newLabel" required maxlength="255" class="w-full border rounded-lg px-3 py-2" placeholder="Tropa, músico…" />
            </div>
            <div>
              <label :for="`new-total-${unit.id}`" class="block text-sm font-medium text-gray-700 mb-1">Cantidad total</label>
              <input :id="`new-total-${unit.id}`" v-model.number="newTotal" type="number" min="1" step="1" required class="w-full border rounded-lg px-3 py-2" />
            </div>
            <button type="submit" :disabled="saving" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50">Añadir</button>
            <button type="button" class="px-4 py-2 rounded-lg border" @click="showAddForm = false">Cancelar</button>
          </div>
          <p v-if="error" class="text-sm text-red-700 mt-2" role="alert">{{ error }}</p>
        </form>

        <div v-if="unit.components.length" class="space-y-3">
          <section v-for="component in unit.components" :key="component.id" class="border border-gray-200 rounded-lg p-3 sm:p-4">
            <form v-if="editingId === component.id" class="grid gap-3 sm:grid-cols-[1fr_9rem_auto_auto] sm:items-end" @submit.prevent="saveEdit(component)">
              <div>
                <label :for="`edit-label-${component.id}`" class="block text-sm font-medium text-gray-700 mb-1">Etiqueta</label>
                <input :id="`edit-label-${component.id}`" v-model="editLabel" required maxlength="255" class="w-full border rounded-lg px-3 py-2" />
              </div>
              <div>
                <label :for="`edit-total-${component.id}`" class="block text-sm font-medium text-gray-700 mb-1">Cantidad total</label>
                <input :id="`edit-total-${component.id}`" v-model.number="editTotal" type="number" :min="Math.max(1, component.quantityPainted)" step="1" required class="w-full border rounded-lg px-3 py-2" />
              </div>
              <button type="submit" :disabled="saving" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50">Guardar</button>
              <button type="button" class="px-4 py-2 rounded-lg border" @click="editingId = null">Cancelar</button>
            </form>
            <template v-else>
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                  <h3 class="font-medium text-gray-900">{{ component.label }}</h3>
                  <p class="text-sm text-gray-600">{{ component.quantityPainted }}/{{ component.quantityTotal }} pintados</p>
                </div>
                <div class="flex items-center gap-3">
                  <button type="button" class="text-sm text-blue-700 hover:underline" @click="startEdit(component)">Editar</button>
                  <button v-if="removingId !== component.id" type="button" class="text-sm text-red-700 hover:underline" @click="removingId = component.id">Quitar</button>
                  <span v-else class="flex items-center gap-2 text-sm" role="group" :aria-label="`Confirmar quitar ${component.label}`">
                    <span>¿Quitar?</span>
                    <button type="button" class="font-semibold text-red-700 hover:underline" :disabled="saving" @click="removeComponent(component)">Sí</button>
                    <button type="button" class="text-gray-600 hover:underline" @click="removingId = null">No</button>
                  </span>
                </div>
              </div>

              <div v-if="component.quantityTotal <= CHECKBOX_LIMIT" class="mt-3 flex flex-wrap gap-1" role="group" :aria-label="`Miniaturas pintadas de ${component.label}`">
                <label v-for="index in component.quantityTotal" :key="index" class="inline-flex w-10 h-10 items-center justify-center cursor-pointer rounded focus-within:ring-2 focus-within:ring-blue-500" :title="`Miniatura ${index} de ${component.quantityTotal}`">
                  <input
                    type="checkbox"
                    class="w-6 h-6 rounded border-gray-400 text-blue-700 focus:ring-blue-500"
                    :checked="isSlotPainted(component, index)"
                    :disabled="savingComponentId === component.id"
                    :aria-label="`Miniatura ${index} de ${component.quantityTotal} de ${component.label}, ${isSlotPainted(component, index) ? 'pintada' : 'sin pintar'}`"
                    @change="toggleMiniature(component, index, $event)"
                  />
                </label>
              </div>
              <div v-else class="mt-3 flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-700">Pintadas:</span>
                <button type="button" class="w-9 h-9 border rounded-lg text-lg disabled:opacity-40" :disabled="component.quantityPainted === 0 || savingComponentId === component.id" :aria-label="`Restar una miniatura pintada de ${component.label}`" @click="setPainted(component, component.quantityPainted - 1)">−</button>
                <input
                  type="number"
                  class="w-20 h-9 border rounded-lg px-2 text-center"
                  min="0"
                  :max="component.quantityTotal"
                  :value="component.quantityPainted"
                  :disabled="savingComponentId === component.id"
                  :aria-label="`Cantidad pintada de ${component.label}`"
                  @change="setPaintedFromInput(component, $event)"
                />
                <button type="button" class="w-9 h-9 border rounded-lg text-lg disabled:opacity-40" :disabled="component.quantityPainted === component.quantityTotal || savingComponentId === component.id" :aria-label="`Añadir una miniatura pintada de ${component.label}`" @click="setPainted(component, component.quantityPainted + 1)">+</button>
                <span class="text-sm text-gray-600">de {{ component.quantityTotal }}</span>
              </div>
            </template>
          </section>
        </div>
        <p v-else class="rounded-lg bg-gray-50 border border-dashed p-4 text-sm text-gray-600">Esta unidad todavía no tiene componentes. Añade una etiqueta y una cantidad para empezar.</p>
        <p v-if="error && !showAddForm" class="text-sm text-red-700 mt-3" role="alert">{{ error }}</p>
      </section>

      <PaintingPlanPanel :unit="unit" />
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import PaintingPlanPanel from '@/components/armies/PaintingPlanPanel.vue'
import { formatHours } from '@/utils/format'
import type { ProjectUnit, UnitComponent } from '@/types/models'

const props = defineProps<{ unit: ProjectUnit; initiallyExpanded?: boolean }>()
const CHECKBOX_LIMIT = 40
const store = useProjectStore()
const expanded = ref(props.initiallyExpanded ?? false)
const showAddForm = ref(false)
const newLabel = ref('')
const newTotal = ref(1)
const saving = ref(false)
const error = ref('')
const savingComponentId = ref<string | null>(null)
const editingId = ref<string | null>(null)
const editLabel = ref('')
const editTotal = ref(1)
const removingId = ref<string | null>(null)
const paintedSlots = ref<Record<string, number[]>>({})

const totalCount = computed(() => props.unit.components.reduce((sum, component) => sum + component.quantityTotal, 0))
const paintedTotal = computed(() => props.unit.components.reduce((sum, component) => sum + component.quantityPainted, 0))
const componentCountLabel = computed(() => `${props.unit.components.length} ${props.unit.components.length === 1 ? 'componente' : 'componentes'}`)

async function addComponent() {
  const label = newLabel.value.trim()
  if (!label || !Number.isInteger(newTotal.value) || newTotal.value < 1) return
  saving.value = true
  error.value = ''
  try {
    await store.addComponent(props.unit.id, label, newTotal.value)
    newLabel.value = ''
    newTotal.value = 1
    showAddForm.value = false
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo añadir el componente.'
  } finally {
    saving.value = false
  }
}

function startEdit(component: UnitComponent) {
  editingId.value = component.id
  editLabel.value = component.label
  editTotal.value = component.quantityTotal
  error.value = ''
}

async function saveEdit(component: UnitComponent) {
  if (!editLabel.value.trim() || !Number.isInteger(editTotal.value) || editTotal.value < component.quantityPainted || editTotal.value < 1) {
    error.value = `La cantidad total debe ser al menos ${Math.max(1, component.quantityPainted)}.`
    return
  }
  saving.value = true
  error.value = ''
  try {
    await store.updateComponent({ ...component, label: editLabel.value.trim(), quantityTotal: editTotal.value })
    delete paintedSlots.value[component.id]
    editingId.value = null
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo actualizar el componente.'
  } finally {
    saving.value = false
  }
}

async function removeComponent(component: UnitComponent) {
  saving.value = true
  error.value = ''
  try {
    await store.removeComponent(component)
    removingId.value = null
  } catch (e: any) {
    error.value = e.response?.data?.error ?? 'No se pudo quitar el componente.'
  } finally {
    saving.value = false
  }
}

async function toggleMiniature(component: UnitComponent, index: number, event: Event) {
  const isChecked = (event.target as HTMLInputElement).checked
  const currentSlots = getPaintedSlots(component)
  const wasChecked = currentSlots.includes(index)
  if (isChecked === wasChecked) return
  const nextSlots = isChecked
    ? [...currentSlots, index].sort((a, b) => a - b)
    : currentSlots.filter((slot) => slot !== index)
  await setPainted(component, nextSlots.length, nextSlots)
}

function getPaintedSlots(component: UnitComponent): number[] {
  const cached = paintedSlots.value[component.id]
  if (cached && cached.length === component.quantityPainted && cached.every((slot) => slot <= component.quantityTotal)) return cached
  return Array.from({ length: component.quantityPainted }, (_, index) => index + 1)
}

function isSlotPainted(component: UnitComponent, index: number): boolean {
  const cached = paintedSlots.value[component.id]
  if (cached && cached.length === component.quantityPainted && cached.every((slot) => slot <= component.quantityTotal)) return cached.includes(index)
  return index <= component.quantityPainted
}

function setPaintedFromInput(component: UnitComponent, event: Event) {
  const input = event.target as HTMLInputElement
  if (input.value.trim() === '') {
    input.value = String(component.quantityPainted)
    return
  }
  const value = Number(input.value)
  if (!Number.isInteger(value) || value < 0 || value > component.quantityTotal) {
    input.value = String(component.quantityPainted)
    return
  }
  void setPainted(component, value)
}

function formatEstimatedDate(date: string): string {
  return new Intl.DateTimeFormat('es-ES', { dateStyle: 'medium' }).format(new Date(`${date}T00:00:00`))
}

async function setPainted(component: UnitComponent, nextValue: number, nextSlots?: number[]) {
  if (savingComponentId.value === component.id || nextValue < 0 || nextValue > component.quantityTotal) return
  if (nextValue === component.quantityPainted && !nextSlots) return
  const previousSlots = paintedSlots.value[component.id]
  if (nextSlots) paintedSlots.value = { ...paintedSlots.value, [component.id]: nextSlots }
  savingComponentId.value = component.id
  error.value = ''
  try {
    const delta = nextValue - component.quantityPainted
    await store.adjustPaintedQuantity(component.id, delta)
    if (!nextSlots) delete paintedSlots.value[component.id]
  } catch (e: any) {
    if (nextSlots) {
      if (previousSlots) paintedSlots.value = { ...paintedSlots.value, [component.id]: previousSlots }
      else delete paintedSlots.value[component.id]
    }
    error.value = e.response?.data?.error ?? `No se pudo actualizar el progreso de ${component.label}.`
  } finally {
    savingComponentId.value = null
  }
}
</script>
