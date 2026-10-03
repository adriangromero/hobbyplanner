<template>
  <section aria-labelledby="army-inventory-heading">
    <header class="flex flex-wrap items-end justify-between gap-4 mb-4">
      <div>
        <h2 id="army-inventory-heading" class="text-xl font-semibold">Inventario del ejército</h2>
        <p class="text-gray-600 mt-1">Unidades, composición y progreso de pintado.</p>
      </div>
      <button
        v-if="!showUnitForm"
        type="button"
        class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium"
        @click="showUnitForm = true"
      >
        Añadir unidad
      </button>
    </header>

    <form v-if="showUnitForm" class="bg-white border rounded-xl p-4 mb-5" @submit.prevent="createUnit">
      <label for="unit-name" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la unidad o personaje</label>
      <div class="flex flex-wrap gap-2">
        <input id="unit-name" v-model="unitName" required maxlength="255" class="flex-1 min-w-52 border rounded-lg px-3 py-2" placeholder="Ej. Guardia del bosque" />
        <button type="submit" :disabled="savingUnit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50">
          {{ savingUnit ? 'Guardando…' : 'Añadir' }}
        </button>
        <button type="button" class="px-4 py-2 rounded-lg border" @click="cancelUnit">Cancelar</button>
      </div>
      <p class="text-sm text-gray-600 mt-2">Después podrás desglosar la unidad por tipo de miniatura, por ejemplo tropa, músico y campeón.</p>
      <p v-if="formError" class="text-red-600 text-sm mt-2" role="alert">{{ formError }}</p>
    </form>

    <div v-if="units.length" class="space-y-3">
      <ArmyUnitCard v-for="unit in units" :key="unit.id" :unit="unit" :initially-expanded="expandedUnitId === unit.id" />
    </div>
    <div v-else class="bg-white border rounded-xl px-6 py-12 text-center">
      <h3 class="text-lg font-semibold">Todavía no hay unidades</h3>
      <p class="text-gray-600 mt-1 mb-4">Añade una unidad o personaje para empezar a organizar sus miniaturas.</p>
      <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white" @click="showUnitForm = true">Añadir primera unidad</button>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import ArmyUnitCard from '@/components/armies/ArmyUnitCard.vue'

const props = defineProps<{ projectId: string }>()
const store = useProjectStore()
const units = computed(() => store.units)
const showUnitForm = ref(false)
const unitName = ref('')
const savingUnit = ref(false)
const formError = ref('')
const expandedUnitId = ref<string | null>(null)

function cancelUnit() {
  showUnitForm.value = false
  unitName.value = ''
  formError.value = ''
}

async function createUnit() {
  const name = unitName.value.trim()
  if (!name) return
  savingUnit.value = true
  formError.value = ''
  try {
    const unit = await store.createUnit(props.projectId, name)
    expandedUnitId.value = unit.id
    cancelUnit()
  } catch (e: any) {
    formError.value = e.response?.data?.error ?? 'No se pudo añadir la unidad.'
  } finally {
    savingUnit.value = false
  }
}
</script>
