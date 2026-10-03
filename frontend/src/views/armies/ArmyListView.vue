<template>
  <section class="max-w-5xl mx-auto">
    <header class="flex flex-wrap items-end justify-between gap-4 mb-6">
      <div>
        <h1 class="text-3xl font-bold">Ejércitos</h1>
        <p class="text-gray-600 mt-1">Organiza tus fuerzas y el progreso de pintado.</p>
      </div>
      <button
        v-if="!showCreateForm"
        type="button"
        class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium"
        @click="showCreateForm = true"
      >
        Crear ejército
      </button>
    </header>

    <form v-if="showCreateForm" class="bg-white border rounded-xl p-4 mb-5" @submit.prevent="createArmy">
      <label for="army-name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del ejército</label>
      <div class="flex flex-wrap gap-2">
        <input
          id="army-name"
          v-model="name"
          autofocus
          required
          maxlength="255"
          class="flex-1 min-w-52 border rounded-lg px-3 py-2"
          placeholder="Ej. Ejército del Imperio, Varios"
        />
        <button type="submit" :disabled="saving" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
        <button type="button" class="px-4 py-2 rounded-lg border" @click="cancelCreate">Cancelar</button>
      </div>
      <p v-if="formError" class="text-red-600 text-sm mt-2" role="alert">{{ formError }}</p>
    </form>

    <p v-if="store.error" class="text-red-700 bg-red-50 rounded-lg p-3 mb-4" role="alert">{{ store.error }}</p>
    <p v-if="store.loading" class="text-gray-600 py-8 text-center" role="status">Cargando ejércitos…</p>

    <div v-else-if="store.armies.length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <RouterLink
        v-for="army in store.armies"
        :key="army.id"
        :to="`/armies/${army.id}`"
        class="block bg-white border rounded-xl p-5 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <h2 class="text-lg font-semibold text-gray-900">{{ army.name }}</h2>
        <p class="text-sm text-gray-600 mt-2">Abrir inventario y progreso de pintado <span aria-hidden="true">→</span></p>
      </RouterLink>
    </div>

    <div v-else-if="!store.loading" class="bg-white border rounded-xl px-6 py-12 text-center">
      <h2 class="text-lg font-semibold">Aún no tienes ejércitos</h2>
      <p class="text-gray-600 mt-1 mb-4">Crea uno para empezar a registrar unidades y personajes.</p>
      <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white" @click="showCreateForm = true">
        Crear el primer ejército
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useArmyStore } from '@/stores/armyStore'

const store = useArmyStore()
const showCreateForm = ref(false)
const name = ref('')
const saving = ref(false)
const formError = ref('')

onMounted(() => store.loadArmies())

function cancelCreate() {
  showCreateForm.value = false
  name.value = ''
  formError.value = ''
}

async function createArmy() {
  const value = name.value.trim()
  if (!value) return
  saving.value = true
  formError.value = ''
  try {
    await store.createArmy(value)
    cancelCreate()
  } catch (e: any) {
    formError.value = e.response?.data?.error ?? 'No se pudo crear el ejército.'
  } finally {
    saving.value = false
  }
}
</script>
