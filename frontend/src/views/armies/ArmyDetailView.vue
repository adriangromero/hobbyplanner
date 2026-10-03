<template>
  <div class="max-w-6xl mx-auto">

    <div v-if="project?.type === 'army'">
      <router-link
        to="/armies"
        class="inline-flex items-center gap-1 text-blue-600 hover:underline text-sm"
      >
        ← Volver a ejércitos
      </router-link>

      <div class="flex items-start justify-between gap-4 flex-wrap mt-4 mb-1">
        <div class="flex items-center gap-3">
          <h1 class="text-3xl font-bold">{{ project.name }}</h1>
          <span v-if="project.type === 'army'" class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Ejército</span>
          <span
            v-if="project.status === 'completed'"
            class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700"
          >
            Completado
          </span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button
            v-if="project.type === 'army'"
            type="button"
            @click="armyOptionsOpen = !armyOptionsOpen"
            :aria-expanded="armyOptionsOpen"
            aria-controls="army-options"
            class="text-sm font-medium px-3.5 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
          >
            ⚙ Opciones del ejército
          </button>
          <button
            @click="handleToggleProjectStatus"
            :disabled="togglingStatus"
            class="text-sm font-medium px-4 py-2 rounded-lg transition disabled:opacity-50 shrink-0"
            :class="project.status === 'completed'
              ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              : 'bg-green-600 text-white hover:bg-green-700'"
          >
            {{ project.status === 'completed' ? 'Reactivar ejército' : 'Completar ejército' }}
          </button>
        </div>
      </div>
      <p class="text-gray-600 mb-6 max-w-2xl">{{ project.description }}</p>

      <section
        v-if="project.type === 'army' && armyOptionsOpen"
        id="army-options"
        class="mb-6 rounded-xl border bg-white p-5 shadow-sm"
        aria-labelledby="army-options-heading"
      >
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h2 id="army-options-heading" class="text-lg font-semibold">Opciones del ejército</h2>
            <p class="text-sm text-gray-600 mt-1">Personaliza la ambientación de este ejército. La elección se guarda en este navegador.</p>
          </div>
          <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline" @click="armyOptionsOpen = false">Cerrar opciones</button>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] sm:items-center">
          <label for="army-theme" class="text-sm font-medium text-gray-800">Tema visual</label>
          <select
            id="army-theme"
            :value="themeStore.activeTheme"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
            @change="changeArmyTheme"
          >
            <option value="cozy">Cozy neutral · crema, terracota y salvia</option>
            <option value="imperial">Imperial cozy · pergamino, dorado y letras clásicas</option>
          </select>
        </div>
        <p class="mt-3 text-sm text-gray-600" aria-live="polite">
          {{ themeStore.activeTheme === 'imperial'
            ? 'Pergamino cálido, acentos dorados y tipografía serif clásica, con adornos originales muy sutiles.'
            : 'Crema suave, terracota y salvia con una presentación limpia y legible.' }}
        </p>
      </section>

      <ArmyInventory :project-id="project.id" />
    </div>

    <div v-else-if="project && !store.loading" class="mx-auto max-w-xl rounded-xl border bg-white p-6 text-center">
      <h1 class="text-xl font-semibold">Este registro no es un ejército</h1>
      <p class="mt-2 text-sm text-gray-600">La sección de ejércitos muestra inventario de unidades y progreso de pintado.</p>
      <router-link to="/armies" class="mt-4 inline-flex text-sm font-medium text-blue-700 hover:underline">Volver a ejércitos</router-link>
    </div>

    <div v-else-if="!store.loading" class="text-center text-gray-500">
      Proyecto no encontrado
    </div>

  </div>

  <BlockingOverlay :active="store.loading" message="Cargando..." />
</template>

<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import { useToast } from '@/composables/useToast'
import { projectApi } from '@/api/projectApi'
import ArmyInventory from '@/components/armies/ArmyInventory.vue'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'
import { useThemeStore, type ArmyTheme } from '@/stores/themeStore'

const store = useProjectStore()
const route = useRoute()
const toast = useToast()
const themeStore = useThemeStore()

const togglingStatus = ref(false)
const armyOptionsOpen = ref(false)

const project = computed(() => store.currentProject)

watch(() => route.params.id, (id) => {
  if (typeof id === 'string') void store.loadProject(id)
}, { immediate: true })

watch([() => route.params.id, project], ([id, value]) => {
  themeStore.activateArmy(
    typeof id === 'string' && value?.id === id && value.type === 'army' ? value.id : null,
  )
}, { immediate: true })

onBeforeUnmount(() => themeStore.activateArmy(null))

function changeArmyTheme(event: Event) {
  if (!project.value || project.value.type !== 'army') return
  const theme = (event.target as HTMLSelectElement).value as ArmyTheme
  themeStore.setArmyTheme(project.value.id, theme)
}

async function handleToggleProjectStatus() {
  if (!project.value) return
  togglingStatus.value = true

  try {
    const data = await projectApi.toggleStatus(project.value.id)
    store.toggleProjectStatus(data.status)
    store.refreshEstimation()
    toast.success(
      data.status === 'completed'
        ? `Ejército "${data.name}" completado`
        : `Ejército "${data.name}" reactivado`
    )
  } catch {
    toast.error('Error al cambiar el estado del ejército')
  } finally {
    togglingStatus.value = false
  }
}
</script>
