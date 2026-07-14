<template>
  <tr
    class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50/80 transition-colors"
    :class="{ 'opacity-50 bg-green-50/50': isCompleted }"
  >

    <!-- Selección -->
    <td class="p-3 w-8 pl-4">
      <input
        type="checkbox"
        :checked="selected"
        @change="$emit('toggle-select')"
        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-400 cursor-pointer"
      />
    </td>

    <!-- Estado -->
    <td class="p-3 w-24">
      <button
        @click="handleToggleStatus"
        :disabled="loading"
        class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 disabled:opacity-50 mx-auto"
        :class="isCompleted
          ? 'bg-green-500 border-green-500 text-white'
          : 'border-gray-300 hover:border-green-400 hover:scale-110'"
        :title="isCompleted ? 'Marcar como pendiente' : 'Marcar como completado'"
      >
        <svg v-if="isCompleted" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
          <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.4 7.4a1 1 0 01-1.4 0L3.3 9.5a1 1 0 111.4-1.4l3.6 3.6 6.7-6.7a1 1 0 011.4 0z" clip-rule="evenodd" />
        </svg>
      </button>
    </td>

    <!-- Nombre -->
    <td class="p-3">
      <div v-if="editingItem">
        <input
          v-model="editForm.name"
          type="text"
          class="border rounded px-2 py-1 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>
      <span v-else class="font-medium text-gray-800" :class="{ 'line-through text-gray-400 font-normal': isCompleted }">
        {{ item.name }}
      </span>
    </td>

    <!-- Horas estimadas -->
    <td class="p-3">
      <div v-if="editingItem" class="flex items-center gap-1.5">
        <input
          v-model="editForm.estimatedHours"
          type="number"
          min="0.5"
          step="0.5"
          class="border rounded px-2 py-1 text-sm w-16 focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>
      <span v-else class="text-gray-600">
        {{ item.estimatedHours }}h
      </span>
    </td>

    <!-- Sesiones — con botón para abrir modal -->
    <td class="p-3">
      <button
        @click="showSessionsModal = true"
        class="flex items-center gap-1.5 text-sm text-gray-600 hover:text-blue-600 transition-colors"
        title="Ver sesiones"
      >
        <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-gray-400">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.3.7l2.5 2.5a1 1 0 001.4-1.4L11 9.6V6z" clip-rule="evenodd" />
        </svg>
        <span>{{ item.totalSessions }}</span>
      </button>
    </td>

    <!-- Horas trabajadas -->
    <td class="p-3 text-sm text-gray-600">
      {{ formatHours(item.totalHours) }}
    </td>

    <!-- Acciones -->
    <td class="p-3 pr-4">
      <div class="flex items-center justify-end gap-1.5">

        <!-- Timer corriendo -->
        <span v-if="isActive" class="font-mono text-xs text-red-600 font-bold bg-red-50 px-2 py-1 rounded-full">
          {{ timer.elapsedFormatted }}
        </span>

        <!-- Stop con confirm inline -->
        <template v-if="isActive">
          <Transition name="fade" mode="out-in">
            <div v-if="confirmingStop" key="confirm" class="flex items-center gap-1">
              <span class="text-xs text-red-600 font-medium">¿Finalizar?</span>
              <button
                @click="handleStop"
                :disabled="loading"
                class="text-xs bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded transition disabled:opacity-50"
              >
                Sí
              </button>
              <button
                @click="confirmingStop = false"
                class="text-xs text-gray-500 hover:text-gray-700 transition"
              >
                No
              </button>
            </div>
            <button
              v-else
              key="stop-btn"
              @click="confirmingStop = true"
              :disabled="loading"
              class="w-8 h-8 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-sm transition disabled:opacity-50"
              title="Parar sesión"
            >
              <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><rect x="5" y="5" width="10" height="10" rx="1.5" /></svg>
            </button>
          </Transition>
        </template>

        <!-- Start (solo si no está completado) -->
        <button
          v-else-if="!editingItem && !deletingItem && !isCompleted"
          @click="handleStart"
          :disabled="loading || anotherSessionActive"
          :class="anotherSessionActive
            ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
            : 'bg-green-600 hover:bg-green-700 text-white shadow-sm hover:scale-105'"
          class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-150 disabled:opacity-50"
          :title="anotherSessionActive ? 'Sesión activa en ' + timer.activeItemName : 'Iniciar sesión'"
        >
          <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 translate-x-[1px]"><path d="M6 4.5v11l9-5.5-9-5.5z" /></svg>
        </button>

        <!-- Separador -->
        <span class="text-gray-200">|</span>

        <!-- Editar item -->
        <template v-if="editingItem">
          <div v-if="editItemError" class="text-red-500 text-xs">{{ editItemError }}</div>
          <button
            @click="handleUpdateItem"
            :disabled="loading"
            class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded transition disabled:opacity-50"
          >
            &#10003;
          </button>
          <button
            @click="cancelEditItem"
            :disabled="loading"
            class="text-xs text-gray-500 hover:text-gray-700 transition disabled:opacity-50"
          >
            &#10005;
          </button>
        </template>

        <!-- Confirmar eliminar item -->
        <template v-else-if="deletingItem">
          <span class="text-xs text-red-600 font-medium">¿Eliminar item?</span>
          <button
            @click="handleDeleteItem"
            class="text-xs bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded transition"
          >
            Sí
          </button>
          <button
            @click="deletingItem = false"
            class="text-xs text-gray-500 hover:text-gray-700 transition"
          >
            No
          </button>
        </template>

        <template v-else>
          <button
            @click="startEditItem"
            class="w-8 h-8 rounded-lg text-blue-400 hover:text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors"
            title="Editar item"
          >
            <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
              <path d="M13.6 2.4a1.5 1.5 0 012.1 2.1l-1 1-2.1-2.1 1-1zM4 12.5l7.6-7.6 2.1 2.1L6.1 14.6H4v-2.1z" />
            </svg>
          </button>
          <button
            @click="deletingItem = true"
            class="w-8 h-8 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors"
            title="Eliminar item"
          >
            <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
              <path fill-rule="evenodd" d="M8 2a1 1 0 00-1 1v1H4a1 1 0 000 2h12a1 1 0 100-2h-3V3a1 1 0 00-1-1H8zM5 7l.6 9.1a2 2 0 002 1.9h4.8a2 2 0 002-1.9L15 7H5z" clip-rule="evenodd" />
            </svg>
          </button>
        </template>

      </div>
    </td>
  </tr>

  <BlockingOverlay :active="loading" :message="loadingMessage" :detail="item.name" />

  <SessionsModal v-model="showSessionsModal" :item="item" />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTimerStore } from '@/stores/timerStore'
import { useProjectStore } from '@/stores/projectStore'
import { useBlockingAction } from '@/composables/useBlockingAction'
import { useToast } from '@/composables/useToast'
import { itemApi } from '@/api/itemApi'
import { formatHours } from '@/utils/format'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'
import SessionsModal from './SessionsModal.vue'
import type { Item } from '@/types/models'

const props = defineProps<{
  item:     Item
  selected: boolean
}>()

defineEmits<{
  'toggle-select': []
}>()

const timer        = useTimerStore()
const projectStore = useProjectStore()
const toast        = useToast()
const { loading, loadingMessage, run } = useBlockingAction()

// Timer
const confirmingStop = ref(false)

// Item edit/delete
const editingItem   = ref(false)
const deletingItem  = ref(false)
const editItemError = ref<string | null>(null)
const editForm      = ref({ name: '', estimatedHours: '' })

// Sessions modal
const showSessionsModal = ref(false)

const isActive = computed(() =>
  timer.isRunning && timer.activeItemId === props.item.id
)

const anotherSessionActive = computed(() =>
  timer.isRunning && timer.activeItemId !== props.item.id
)

const isCompleted = computed(() => props.item.status === 'completed')


// ── Estado ──────────────────────────────────────────────

async function handleToggleStatus() {
  await run('Actualizando estado...', async () => {
    try {
      const data = await itemApi.toggleStatus(props.item.id)
      projectStore.updateItemStatus(props.item.id, data.status)

      toast.success(
        data.status === 'completed'
          ? `"${props.item.name}" completado`
          : `"${props.item.name}" reactivado`
      )
    } catch {
      toast.error('Error al actualizar el estado')
    }
  })
}


// ── Item ────────────────────────────────────────────────

function startEditItem() {
  editingItem.value   = true
  deletingItem.value  = false
  editItemError.value = null
  editForm.value = {
    name:           props.item.name,
    estimatedHours: String(props.item.estimatedHours),
  }
}

function cancelEditItem() {
  editingItem.value   = false
  editItemError.value = null
  editForm.value      = { name: '', estimatedHours: '' }
}

async function handleUpdateItem() {
  editItemError.value = null

  if (!editForm.value.name.trim()) {
    editItemError.value = 'El nombre es obligatorio'
    return
  }

  if (parseFloat(editForm.value.estimatedHours) <= 0) {
    editItemError.value = 'Las horas deben ser mayores a 0'
    return
  }

  await run('Guardando item...', async () => {
    try {
      const data = await itemApi.update(
        props.item.id,
        editForm.value.name.trim(),
        parseFloat(editForm.value.estimatedHours),
      )

      projectStore.updateItem({
        id:             data.id,
        name:           data.name,
        estimatedHours: data.estimatedHours,
      })

      cancelEditItem()
      toast.success(`Item "${data.name}" actualizado`)

    } catch (e: any) {
      editItemError.value = e.response?.data?.error ?? 'Error al actualizar'
    }
  })
}

async function handleDeleteItem() {
  await run('Eliminando item...', async () => {
    try {
      await itemApi.remove(props.item.id)
      projectStore.removeItem(props.item.id)
      toast.success(`Item "${props.item.name}" eliminado`)
    } catch {
      toast.error('Error al eliminar el item')
    }
  })
}

// ── Timer ────────────────────────────────────────────────

async function handleStart() {
  if (anotherSessionActive.value) {
    toast.error('Finaliza la sesión de "' + timer.activeItemName + '" primero')
    return
  }

  await run('Iniciando sesión...', async () => {
    try {
      const projectId = projectStore.currentProject?.id
      if (!projectId) return
      await timer.start(props.item.id, props.item.name, projectId)
      toast.success(`Sesión iniciada — ${props.item.name}`)
    } catch {
      toast.error('Error al iniciar la sesión')
    }
  })
}

async function handleStop() {
  confirmingStop.value = false

  await run('Finalizando sesión...', async () => {
    const session = await timer.stop()

    if (!session) {
      toast.error('Error al guardar la sesión')
      return
    }

    projectStore.addSessionToItem(props.item.id, session)
    toast.success(`Sesión finalizada — ${formatHours(session.durationHours)}`)
  })
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
