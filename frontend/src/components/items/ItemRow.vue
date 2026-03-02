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
      {{ formatHours(totalHours) }}
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
            @click="confirmingStart = true"
            :disabled="loading"
            class="text-green-600 hover:text-green-700 transition-colors disabled:opacity-50"
            title="Iniciar sesión"
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
            :disabled="editItemLoading"
            class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded transition disabled:opacity-50"
          >
            {{ editItemLoading ? '...' : '✓' }}
          </button>
          <button
            @click="cancelEditItem"
            class="text-xs text-gray-500 hover:text-gray-700 transition"
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

  <!-- Overlay bloqueante -->
  <Teleport to="body">
    <div
      v-if="loading"
      class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[9998]"
    >
      <div class="bg-white rounded-xl shadow-2xl px-10 py-8 flex flex-col items-center gap-4">
        <div class="w-10 h-10 border-4 border-gray-200 border-t-red-600 rounded-full animate-spin" />
        <p class="text-gray-700 font-semibold text-lg">{{ loadingMessage }}</p>
        <p class="text-gray-400 text-sm">{{ item.name }}</p>
      </div>
    </div>
  </Teleport>

  <!-- Modal sesiones -->
  <Teleport to="body">
    <div
      v-if="showSessionsModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeSessionsModal"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">

        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Sesiones — {{ item.name }}</h3>
          <button @click="closeSessionsModal" class="text-gray-400 hover:text-gray-700">✕</button>
        </div>

        <div v-if="item.sessions.length === 0" class="text-gray-400 text-sm py-4 text-center">
          No hay sesiones registradas
        </div>

        <div v-else class="space-y-2 max-h-80 overflow-y-auto">
          <div v-for="session in item.sessions" :key="session.id">

            <!-- Vista normal -->
            <div
              v-if="editingSessionId !== session.id && deletingSessionId !== session.id"
              class="flex justify-between items-center p-3 bg-gray-50 rounded-lg text-sm"
            >
              <div class="text-gray-600">
                <span>{{ formatDate(session.startedAt) }}</span>
                <span class="mx-2 text-gray-400">→</span>
                <span>{{ session.endedAt ? formatDate(session.endedAt) : 'En curso' }}</span>
              </div>
              <div class="flex items-center gap-3">
                <span class="font-medium text-gray-800">{{ formatHours(session.durationHours) }}</span>
                <button @click="startEditSession(session)" class="text-blue-400 hover:text-blue-600 transition-colors" title="Editar">✏️</button>
                <button @click="deletingSessionId = session.id" class="text-red-400 hover:text-red-600 transition-colors" title="Eliminar">🗑️</button>
              </div>
            </div>

            <!-- Confirmar eliminar sesión -->
            <div
              v-else-if="deletingSessionId === session.id"
              class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm flex justify-between items-center"
            >
              <span class="text-red-700 font-medium">¿Eliminar esta sesión?</span>
              <div class="flex gap-2">
                <button @click="deletingSessionId = null" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition">Cancelar</button>
                <button @click="handleDeleteSession(session.id)" class="px-3 py-1 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition">Eliminar</button>
              </div>
            </div>

            <!-- Editar sesión -->
            <div
              v-else
              class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm space-y-2"
            >
              <div class="flex gap-2 items-center">
                <label class="text-gray-600 w-16 shrink-0">Inicio</label>
                <input v-model="editSessionForm.startedAt" type="datetime-local" class="flex-1 border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
              </div>
              <div class="flex gap-2 items-center">
                <label class="text-gray-600 w-16 shrink-0">Fin</label>
                <input v-model="editSessionForm.endedAt" type="datetime-local" class="flex-1 border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
              </div>
              <div v-if="editSessionError" class="text-red-500 text-xs">{{ editSessionError }}</div>
              <div class="flex gap-2 justify-end pt-1">
                <button @click="cancelEditSession" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition">Cancelar</button>
                <button @click="handleUpdateSession(session.id)" :disabled="editSessionLoading" class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded transition disabled:opacity-50">
                  {{ editSessionLoading ? '...' : 'Guardar' }}
                </button>
              </div>
            </div>

          </div>
        </div>

        <div v-if="item.sessions.length > 0" class="mt-4 pt-4 border-t flex justify-end">
          <span class="text-sm text-gray-500">
            Total: <strong>{{ formatHours(totalHours) }}</strong>
          </span>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTimerStore } from '@/stores/timerStore'
import { useProjectStore } from '@/stores/projectStore'
import { useToast } from '@/composables/useToast'
import api from '@/api/axios'

interface Session {
  id:            string
  startedAt:     string
  endedAt:       string | null
  durationHours: number
}

interface Item {
  id:             string
  name:           string
  estimatedHours: number
  totalSessions:  number
  sessions:       Session[]
}

const props = defineProps<{ item: Item }>()

const timer        = useTimerStore()
const projectStore = useProjectStore()
const toast        = useToast()

// Timer
const loading         = ref(false)
const loadingMessage  = ref('')
const confirmingStart = ref(false)
const confirmingStop  = ref(false)

// Item edit/delete
const editingItem      = ref(false)
const deletingItem     = ref(false)
const editItemLoading  = ref(false)
const editItemError    = ref<string | null>(null)
const editForm         = ref({ name: '', estimatedHours: '' })

// Sessions modal
const showSessionsModal  = ref(false)
const editingSessionId   = ref<string | null>(null)
const deletingSessionId  = ref<string | null>(null)
const editSessionLoading = ref(false)
const editSessionError   = ref<string | null>(null)
const editSessionForm    = ref({ startedAt: '', endedAt: '' })

const isActive = computed(() =>
  timer.isRunning && timer.activeItemId === props.item.id
)

const totalHours = computed(() =>
  props.item.sessions
    .filter(s => s.endedAt !== null)
    .reduce((sum, s) => sum + s.durationHours, 0)
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

  editItemLoading.value = true

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
  } finally {
    editItemLoading.value = false
  }
}

async function handleDeleteItem() {
  loading.value        = true
  loadingMessage.value = 'Eliminando item...'

  try {
    await api.delete(`/items/${props.item.id}`)
    projectStore.removeItem(props.item.id)
    toast.success(`Item "${props.item.name}" eliminado`)
  } catch {
    toast.error('Error al eliminar el item')
  } finally {
    loading.value = false
  }
}

// ── Timer ────────────────────────────────────────────────

async function handleStart() {
  confirmingStart.value = false
  loading.value         = true
  loadingMessage.value  = 'Iniciando sesión...'

  try {
    await timer.start(props.item.id, props.item.name, projectStore.currentProject!.id)
    toast.success(`Sesión iniciada — ${props.item.name}`)
  } catch {
    toast.error('Error al iniciar la sesión')
  } finally {
    loading.value = false
  }
}

async function handleStop() {
  confirmingStop.value = false
  loading.value        = true
  loadingMessage.value = 'Finalizando sesión...'

  const session = await timer.stop()

  if (session) {
    projectStore.addSessionToItem(props.item.id, {
      id:            session.id,
      startedAt:     session.startedAt,
      endedAt:       session.endedAt,
      durationHours: session.durationHours,
    })
    toast.success(`Sesión guardada — ${formatHours(session.durationHours)}`)
  } else {
    toast.error('Error al guardar la sesión')
  }

  loading.value = false
}

// ── Sessions ────────────────────────────────────────────────

function closeSessionsModal() {
  showSessionsModal.value = false
  deletingSessionId.value = null
  cancelEditSession()
}

function startEditSession(session: Session) {
  deletingSessionId.value = null
  editingSessionId.value  = session.id
  editSessionError.value  = null
  editSessionForm.value = {
    startedAt: toDatetimeLocal(session.startedAt),
    endedAt:   session.endedAt ? toDatetimeLocal(session.endedAt) : '',
  }
}

function cancelEditSession() {
  editingSessionId.value = null
  editSessionError.value = null
  editSessionForm.value  = { startedAt: '', endedAt: '' }
}

async function handleUpdateSession(sessionId: string) {
  editSessionLoading.value = true
  editSessionError.value   = null

  try {
    const { data } = await api.put(`/work-sessions/${sessionId}`, {
      startedAt: new Date(editSessionForm.value.startedAt).toISOString(),
      endedAt:   editSessionForm.value.endedAt
        ? new Date(editSessionForm.value.endedAt).toISOString()
        : null,
    })

    projectStore.updateSession(props.item.id, {
      id:            data.id,
      startedAt:     data.startedAt,
      endedAt:       data.endedAt,
      durationHours: data.durationHours,
    })

    cancelEditSession()
    toast.success('Sesión actualizada correctamente')

  } catch (e: any) {
    editSessionError.value = e.response?.data?.error ?? 'Error al guardar'
    toast.error(editSessionError.value!)
  } finally {
    editSessionLoading.value = false
  }
}

async function handleDeleteSession(sessionId: string) {
  loading.value        = true
  loadingMessage.value = 'Eliminando sesión...'

  try {
    await api.delete(`/work-sessions/${sessionId}`)
    projectStore.removeSessionFromItem(props.item.id, sessionId)
    deletingSessionId.value = null
    toast.success('Sesión eliminada correctamente')
  } catch {
    toast.error('Error al eliminar la sesión')
  } finally {
    loading.value = false
  }
}

// ── Utils ────────────────────────────────────────────────

function toDatetimeLocal(isoString: string): string {
  return new Date(isoString).toISOString().slice(0, 16)
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleString('es-ES', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function formatHours(hours: number): string {
  const h = Math.floor(hours)
  const m = Math.round((hours - h) * 60)
  return m > 0 ? `${h}h ${m}m` : `${h}h`
}
</script>