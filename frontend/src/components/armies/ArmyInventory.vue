<template>
  <section aria-labelledby="army-inventory-heading">
    <header class="mb-4 flex flex-wrap items-end justify-between gap-4">
      <div>
        <h2 id="army-inventory-heading" class="text-xl font-semibold">Inventario del ejército</h2>
        <p class="mt-1 text-gray-600">Unidades, composición y progreso de pintado.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button v-if="!showUnitForm" type="button" class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700" @click="showUnitForm = true">Añadir unidad</button>
      </div>
    </header>

    <dl class="mb-5 grid grid-cols-2 gap-3 rounded-xl border bg-white p-4 xl:grid-cols-4">
      <div><dt class="text-sm text-gray-600">Unidades</dt><dd class="mt-1 text-xl font-semibold">{{ totalUnits }}</dd></div>
      <div class="border-l pl-4"><dt class="text-sm text-gray-600">Miniaturas en inventario</dt><dd class="mt-1 text-xl font-semibold">{{ totalMiniatures }}</dd></div>
      <div><dt class="text-sm text-gray-600">Miniaturas pintadas</dt><dd class="mt-1 text-xl font-semibold">{{ paintedMiniatures }} <span class="text-sm font-normal text-gray-600">de {{ totalMiniatures }}</span></dd></div>
      <div class="border-l pl-4"><dt class="text-sm text-gray-600">Sesiones de pintado</dt><dd class="mt-1 text-xl font-semibold">{{ totalSessions }}</dd></div>
    </dl>

    <form v-if="showUnitForm" class="mb-5 rounded-xl border bg-white p-4" @submit.prevent="createUnit">
      <h3 class="mb-3 font-semibold">Añadir unidad</h3>
      <div class="grid gap-3 sm:grid-cols-[minmax(12rem,1fr)_minmax(12rem,0.7fr)_minmax(8rem,0.45fr)_auto_auto] sm:items-end">
        <div>
          <label for="unit-name" class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
          <input id="unit-name" v-model="unitName" required maxlength="255" class="w-full rounded-lg border px-3 py-2" placeholder="Ej. Guardia del bosque" />
        </div>
        <div>
          <label for="unit-category" class="mb-1 block text-sm font-medium text-gray-700">Categoría</label>
          <select id="unit-category" v-model="unitCategory" class="w-full rounded-lg border bg-white px-3 py-2">
            <option v-for="category in UNIT_CATEGORIES" :key="category.value" :value="category.value">{{ category.label }}</option>
          </select>
        </div>
        <div>
          <label for="unit-models-per-row" class="mb-1 block text-sm font-medium text-gray-700">Miniaturas por fila</label>
          <input id="unit-models-per-row" v-model.number="modelsPerRow" type="number" min="1" max="12" step="1" required class="w-full rounded-lg border px-3 py-2" />
        </div>
        <button type="submit" :disabled="savingUnit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50">{{ savingUnit ? 'Guardando…' : 'Añadir' }}</button>
        <button type="button" class="rounded-lg border px-4 py-2" @click="cancelUnit">Cancelar</button>
      </div>
      <p class="mt-2 text-sm text-gray-600">Después podrás desglosarla por tipo de miniatura, por ejemplo tropa, músico y campeón.</p>
      <p v-if="formError" class="mt-2 text-sm text-red-700" role="alert">{{ formError }}</p>
    </form>

    <div v-if="!units.length" class="rounded-xl border border-dashed bg-white px-6 py-10 text-center">
      <h3 class="text-lg font-semibold">Todavía no hay unidades</h3>
      <p class="mb-4 mt-1 text-gray-600">Añade una unidad o personaje para empezar a organizar sus miniaturas.</p>
      <button type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" @click="showUnitForm = true">Añadir primera unidad</button>
    </div>

    <section v-if="units.length" class="mt-6 space-y-3" aria-labelledby="army-units-heading">
      <header class="flex flex-wrap items-end justify-between gap-2">
        <div>
          <h3 id="army-units-heading" class="text-lg font-semibold">Unidades del ejército</h3>
          <p class="text-sm text-gray-600">Abre cada tarjeta para ver la composición, ajustar la formación y gestionar las sesiones.</p>
        </div>
        <p class="text-xs text-gray-500">Usa el asa o las flechas para reordenar.</p>
      </header>
      <p v-if="reorderError" class="rounded-lg bg-red-50 p-3 text-sm text-red-800" role="alert">{{ reorderError }}</p>
      <div class="space-y-3">
        <div v-for="(unit, index) in units" :key="unit.id" class="rounded-xl transition" :class="dropTargetId === unit.id && draggedUnitId !== unit.id ? 'ring-2 ring-blue-500 ring-offset-2' : ''" @dragover.prevent="dropTargetId = unit.id" @dragleave="clearDropTarget(unit.id)" @drop.prevent="dropBefore(unit.id)">
          <div class="mb-1 flex items-center justify-end gap-1">
            <button type="button" draggable="true" class="cursor-grab rounded-md border bg-white px-2 py-1 text-sm text-gray-700 active:cursor-grabbing disabled:opacity-50" :disabled="reordering" :aria-label="'Arrastrar ' + unit.name + ' para cambiar su posición'" title="Arrastra para reordenar" @dragstart="startDragging($event, unit.id)" @dragend="finishDragging">⠿ <span class="hidden sm:inline">Mover</span></button>
            <button type="button" class="rounded-md border bg-white px-2 py-1 text-sm hover:bg-gray-100 disabled:opacity-40" :disabled="index === 0 || reordering" :aria-label="'Mover ' + unit.name + ' arriba'" @click="moveUnit(index, -1)">↑</button>
            <button type="button" class="rounded-md border bg-white px-2 py-1 text-sm hover:bg-gray-100 disabled:opacity-40" :disabled="index === units.length - 1 || reordering" :aria-label="'Mover ' + unit.name + ' abajo'" @click="moveUnit(index, 1)">↓</button>
          </div>
          <ArmyUnitCard :unit="unit" />
        </div>
      </div>
    </section>
  </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import ArmyUnitCard from '@/components/armies/ArmyUnitCard.vue'
import { UNIT_CATEGORIES, type UnitCategory } from '@/types/models'

const props = defineProps<{ projectId: string }>()
const store = useProjectStore()
const units = computed(() => store.units)
const totalUnits = computed(() => units.value.length)
const totalMiniatures = computed(() => units.value.reduce((sum, unit) => sum + unit.components.reduce((unitSum, component) => unitSum + component.quantityTotal, 0), 0))
const paintedMiniatures = computed(() => units.value.reduce((sum, unit) => sum + unit.components.reduce((unitSum, component) => unitSum + component.quantityPainted, 0), 0))
const totalSessions = computed(() => units.value.reduce((sum, unit) => sum + (unit.paintingPlan?.sessionCount ?? 0), 0))
const showUnitForm = ref(false)
const unitName = ref('')
const unitCategory = ref<UnitCategory>('infantry')
const modelsPerRow = ref(5)
const savingUnit = ref(false)
const formError = ref('')
const reorderError = ref('')
const draggedUnitId = ref<string | null>(null)
const dropTargetId = ref<string | null>(null)
const reordering = ref(false)

function cancelUnit() {
  showUnitForm.value = false
  unitName.value = ''
  unitCategory.value = 'infantry'
  modelsPerRow.value = 5
  formError.value = ''
}

async function createUnit() {
  const name = unitName.value.trim()
  if (!name) return
  if (!Number.isInteger(modelsPerRow.value) || modelsPerRow.value < 1 || modelsPerRow.value > 12) {
    formError.value = 'Indica entre 1 y 12 miniaturas por fila.'
    return
  }
  savingUnit.value = true
  formError.value = ''
  try {
    await store.createUnit(props.projectId, name, unitCategory.value, modelsPerRow.value)
    cancelUnit()
  } catch (e: any) {
    formError.value = e.response?.data?.error ?? 'No se pudo añadir la unidad.'
  } finally {
    savingUnit.value = false
  }
}

function startDragging(event: DragEvent, unitId: string) {
  draggedUnitId.value = unitId
  reorderError.value = ''
  event.dataTransfer?.setData('text/plain', unitId)
  if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move'
}

function finishDragging() {
  draggedUnitId.value = null
  dropTargetId.value = null
}

function clearDropTarget(unitId: string) {
  if (dropTargetId.value === unitId) dropTargetId.value = null
}

async function dropBefore(targetId: string) {
  const sourceId = draggedUnitId.value
  finishDragging()
  if (!sourceId || sourceId === targetId) return
  const ordered = [...units.value]
  const sourceIndex = ordered.findIndex((unit) => unit.id === sourceId)
  let targetIndex = ordered.findIndex((unit) => unit.id === targetId)
  if (sourceIndex < 0 || targetIndex < 0) return
  const [moved] = ordered.splice(sourceIndex, 1)
  if (sourceIndex < targetIndex) targetIndex -= 1
  ordered.splice(targetIndex, 0, moved)
  await persistOrder(ordered)
}

async function moveUnit(index: number, offset: -1 | 1) {
  const target = index + offset
  if (target < 0 || target >= units.value.length) return
  const ordered = [...units.value]
  const [moved] = ordered.splice(index, 1)
  ordered.splice(target, 0, moved)
  await persistOrder(ordered)
}

async function persistOrder(ordered: typeof units.value) {
  if (reordering.value) return
  reordering.value = true
  reorderError.value = ''
  try {
    await store.reorderUnits(props.projectId, ordered.map((unit) => unit.id))
  } catch (e: any) {
    reorderError.value = e.response?.data?.error ?? 'No se pudo guardar el orden de las unidades.'
  } finally {
    reordering.value = false
    dropTargetId.value = null
  }
}
</script>
