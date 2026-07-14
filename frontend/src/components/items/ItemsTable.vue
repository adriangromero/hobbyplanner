<template>
  <div>
    <div class="flex justify-between items-center mb-3 gap-3 flex-wrap">
      <div class="flex items-center gap-2">
        <h2 class="text-xl font-semibold">Items</h2>
        <span v-if="items.length" class="text-sm text-gray-400">{{ items.length }}</span>
      </div>

      <Transition name="fade" mode="out-in">
        <div
          v-if="selectedIds.size > 0"
          key="bulk"
          class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg pl-3 pr-1.5 py-1.5"
        >
          <span class="text-sm text-red-700 font-medium">
            {{ selectedIds.size }} seleccionado{{ selectedIds.size > 1 ? 's' : '' }}
          </span>

          <template v-if="!confirmingBulkDelete">
            <button
              @click="confirmingBulkDelete = true"
              class="text-xs bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded transition"
            >
              Eliminar
            </button>
          </template>
          <template v-else>
            <span class="text-xs text-red-600">¿Seguro?</span>
            <button
              @click="handleBulkDelete"
              class="text-xs bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded transition"
            >
              Sí
            </button>
            <button @click="confirmingBulkDelete = false" class="text-xs text-gray-500 hover:text-gray-700 px-1">
              No
            </button>
          </template>

          <button
            @click="clearSelection"
            class="text-gray-400 hover:text-gray-600 w-6 h-6 rounded-full hover:bg-red-100 flex items-center justify-center transition"
            title="Cancelar selección"
          >
            <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
              <path fill-rule="evenodd" d="M4.3 4.3a1 1 0 011.4 0L10 8.6l4.3-4.3a1 1 0 111.4 1.4L11.4 10l4.3 4.3a1 1 0 01-1.4 1.4L10 11.4l-4.3 4.3a1 1 0 01-1.4-1.4L8.6 10 4.3 5.7a1 1 0 010-1.4z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <button
          v-else
          key="add"
          @click="showCreateModal = true"
          class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
        >
          <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" /></svg>
          Añadir item
        </button>
      </Transition>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
      <table class="w-full min-w-[640px]">
        <thead class="bg-gray-50 text-left border-b border-gray-100">
          <tr>
            <th class="p-3 w-8 pl-4">
              <input
                ref="selectAllCheckbox"
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-400 cursor-pointer"
                :disabled="items.length === 0"
              />
            </th>
            <th
              class="p-3 w-24 text-center text-xs font-medium text-gray-500 uppercase tracking-wide cursor-pointer hover:text-gray-700 transition-colors select-none"
              @click="toggleSort('status')"
            >
              <span class="inline-flex items-center justify-center gap-1">Estado<span class="text-blue-500 w-3 inline-block">{{ sortArrow('status') }}</span></span>
            </th>
            <th
              class="p-3 text-xs font-medium text-gray-500 uppercase tracking-wide cursor-pointer hover:text-gray-700 transition-colors select-none"
              @click="toggleSort('name')"
            >
              <span class="inline-flex items-center gap-1">Nombre<span class="text-blue-500 w-3 inline-block">{{ sortArrow('name') }}</span></span>
            </th>
            <th
              class="p-3 text-xs font-medium text-gray-500 uppercase tracking-wide cursor-pointer hover:text-gray-700 transition-colors select-none"
              @click="toggleSort('estimatedHours')"
            >
              <span class="inline-flex items-center gap-1">Estimadas<span class="text-blue-500 w-3 inline-block">{{ sortArrow('estimatedHours') }}</span></span>
            </th>
            <th class="p-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Sesiones</th>
            <th class="p-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Trabajadas</th>
            <th class="p-3 pr-4 text-xs font-medium text-gray-500 uppercase tracking-wide text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <ItemRow
            v-for="item in items"
            :key="item.id"
            :item="item"
            :selected="selectedIds.has(item.id)"
            @toggle-select="toggleSelect(item.id)"
          />
          <tr v-if="items.length === 0">
            <td colspan="7" class="p-6 text-center text-gray-400 text-sm">
              Aún no hay items. Añade uno para empezar.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <CreateItemModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
    />

    <BlockingOverlay :active="bulkDeleting" message="Eliminando items..." />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watchEffect } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import { useToast } from '@/composables/useToast'
import { useBlockingAction } from '@/composables/useBlockingAction'
import { itemApi } from '@/api/itemApi'
import ItemRow from './ItemRow.vue'
import CreateItemModal from './CreateItemModal.vue'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'
import type { Item } from '@/types/models'

const props = defineProps<{ items: Item[]; projectId: string }>()

const projectStore = useProjectStore()
const toast        = useToast()
const { loading: bulkDeleting, run } = useBlockingAction()

const showCreateModal = ref(false)

// ── Ordenación por columna (delegada al backend vía Doctrine) ──

type SortField = 'name' | 'estimatedHours' | 'status'

const sortBy  = ref<SortField | null>(null)
const sortDir = ref<'asc' | 'desc'>('asc')

function toggleSort(field: SortField) {
  if (sortBy.value === field) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortDir.value = 'asc'
  }
  projectStore.loadProject(props.projectId, sortBy.value, sortDir.value)
}

function sortArrow(field: SortField): string {
  if (sortBy.value !== field) return ''
  return sortDir.value === 'asc' ? '▲' : '▼'
}

// ── Selección múltiple ──────────────────────────────────

const selectedIds          = ref<Set<string>>(new Set())
const confirmingBulkDelete = ref(false)
const selectAllCheckbox    = ref<HTMLInputElement | null>(null)

const allSelected = computed(() =>
  props.items.length > 0 && props.items.every(i => selectedIds.value.has(i.id))
)
const someSelected = computed(() => selectedIds.value.size > 0 && !allSelected.value)

watchEffect(() => {
  if (selectAllCheckbox.value) selectAllCheckbox.value.indeterminate = someSelected.value
})

function toggleSelectAll() {
  selectedIds.value = allSelected.value
    ? new Set()
    : new Set(props.items.map(i => i.id))
}

function toggleSelect(id: string) {
  const next = new Set(selectedIds.value)
  next.has(id) ? next.delete(id) : next.add(id)
  selectedIds.value = next
}

function clearSelection() {
  selectedIds.value = new Set()
  confirmingBulkDelete.value = false
}

async function handleBulkDelete() {
  const ids = Array.from(selectedIds.value)

  await run(`Eliminando ${ids.length} item${ids.length > 1 ? 's' : ''}...`, async () => {
    const results = await Promise.allSettled(ids.map(id => itemApi.remove(id)))

    let okCount = 0
    results.forEach((result, index) => {
      if (result.status === 'fulfilled') {
        projectStore.removeItem(ids[index])
        okCount++
      }
    })

    clearSelection()

    if (okCount === ids.length) {
      toast.success(`${okCount} item${okCount > 1 ? 's' : ''} eliminado${okCount > 1 ? 's' : ''}`)
    } else {
      toast.error(`Se eliminaron ${okCount} de ${ids.length} items`)
    }
  })
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
