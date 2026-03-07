<template>
  <tr class="border-b hover:bg-gray-50">

    <!-- Nombre -->
    <td class="p-3">
      <div v-if="editingItem">
        <input
          v-model="editForm.name"
          type="text"
          class="border rounded px-2 py-1 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>
      <span v-else>{{ item.name }}</span>
    </td>

    <!-- Horas estimadas -->
    <td class="p-3">
      <div v-if="editingItem">
        <input
          v-model="editForm.estimatedHours"
          type="number"
          min="0.5"
          step="0.5"
          class="border rounded px-2 py-1 text-sm w-20 focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>
      <span v-else>{{ item.estimatedHours }}h</span>
    </td>

    <!-- Sesiones — con botón para abrir modal -->
    <td class="p-3">
      <button
        @click="showSessionsModal = true"
        class="flex items-center gap-1 text-sm text-gray-600 hover:text-blue-600 transition-colors"
        title="Ver sesiones"
      >
        <span>{{ item.totalSessions }}</span>
        <span class="text-xs">🗂</span>
      </button>
    </td>

    <!-- Horas trabajadas -->
    <td class="p-3 text-sm text-gray-600">
      {{ formatHours(item.totalHours) }}
    </td>

    <!-- Acciones -->
    <td class="p-3">
      <div class="flex items-center gap-2">

        <!-- Timer corriendo -->
        <span v-if="isActive" class="font-mono text-xs text-red-600 font-bold">
          {{ timer.elapsedFormatted }}
        </span>

        <!-- Stop con confirm inline -->
        <template v-if="isActive">
          <template v-if="confirmingStop">
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
          </template>
          <button
            v-else
            @click="confirmingStop = true"
            :disabled="loading"
            class="text-red-600 hover:text-red-700 transition-colors disabled:opacity-50"
            title="Parar sesión"
          >
            ⏹
          </button>
        </template>

        <!-- Start con confirm inline -->
        <template v-else-if="!editingItem && !deletingItem">
          <template v-if="confirmingStart">
            <span class="text-xs text-green-700 font-medium">¿Iniciar?</span>
            <button
              @click="handleStart"
              :disabled="loading"
              class="text-xs bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded transition disabled:opacity-50"
            >
              Sí
            </button>
            <button
              @click="confirmingStart = false"
              class="text-xs text-gray-500 hover:text-gray-700 transition"
            >
              No
            </button>
          </template>
          <button
            v-else
            @click="tryStart"
            :disabled="loading || anotherSessionActive"
            :class="anotherSessionActive ? 'text-gray-300 cursor-not-allowed' : 'text-green-600 hover:text-green-700'"
            class="transition-colors disabled:opacity-50"
            :title="anotherSessionActive ? 'Sesión activa en ' + timer.activeItemName : 'Iniciar sesión'"
          >
            ▶
          </button>
        </template>

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
            ✓
          </button>
          <button
            @click="cancelEditItem"
            :disabled="loading"
            class="text-xs text-gray-500 hover:text-gray-700 transition disabled:opacity-50"
          >
            ✕
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
            class="text-blue-400 hover:text-blue-600 transition-colors"
            title="Editar item"
          >
            ✏️
          </button>
          <button
            @click="deletingItem = true"
            class="text-red-400 hover:text-red-600 transition-colors"
            title="Eliminar item"
          >
            🗑️
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
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'
import SessionsModal from './SessionsModal.vue'
import { formatHours } from '@/utils/format'
import api from '@/api/axios'

interface OpenSession {
  id:        string
  startedAt: string
}

interface Item {
  id:             string
  name:           string
  estimatedHours: number
  totalSessions:  number
  totalHours:     number
  openSession:    OpenSession | null
}

const props = defineProps<{ item: Item }>()

const timer        = useTimerStore()
const projectStore = useProjectStore()
const toast        = useToast()
const { loading, loadingMessage, run } = useBlockingAction()

// Timer
const confirmingStart = ref(false)
const confirmingStop  = ref(false)

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
      const { data } = await api.put(`/items/${props.item.id}`, {
        name:           editForm.value.name.trim(),
        estimatedHours: parseFloat(editForm.value.estimatedHours),
      })

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
      await api.delete(`/items/${props.item.id}`)
      projectStore.removeItem(props.item.id)
      toast.success(`Item "${props.item.name}" eliminado`)
    } catch {
      toast.error('Error al eliminar el item')
    }
  })
}

// ── Timer ────────────────────────────────────────────────

function tryStart() {
  if (anotherSessionActive.value) {
    toast.error('Finaliza la sesión de "' + timer.activeItemName + '" primero')
    return
  }
  confirmingStart.value = true
}

async function handleStart() {
  confirmingStart.value = false

  await run('Iniciando sesión...', async () => {
    try {
      await timer.start(props.item.id, props.item.name, projectStore.currentProject!.id)
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
