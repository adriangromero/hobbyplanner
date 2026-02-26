<template>
  <tr class="border-b hover:bg-gray-50">
    <td class="p-3">{{ item.name }}</td>
    <td class="p-3">{{ item.estimatedHours }}h</td>
    <td class="p-3">{{ item.totalSessions }}</td>
    <td class="p-3">
      <div class="flex items-center gap-2">

        <button
          @click="showModal = true"
          class="text-gray-400 hover:text-gray-700 transition-colors"
          title="Ver sesiones"
        >
          👁
        </button>

        <span v-if="isActive" class="font-mono text-xs text-red-600 font-bold">
          {{ timer.elapsedFormatted }}
        </span>

        <!-- Confirmar Stop inline -->
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

        <!-- Confirmar Start inline -->
        <template v-else>
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
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">

        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Sesiones — {{ item.name }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-700">✕</button>
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
                <button
                  @click="startEdit(session)"
                  class="text-blue-400 hover:text-blue-600 transition-colors"
                  title="Editar"
                >
                  ✏️
                </button>
                <button
                  @click="deletingSessionId = session.id"
                  class="text-red-400 hover:text-red-600 transition-colors"
                  title="Eliminar"
                >
                  🗑️
                </button>
              </div>
            </div>

            <!-- Vista confirmar eliminación -->
            <div
              v-else-if="deletingSessionId === session.id"
              class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm flex justify-between items-center"
            >
              <span class="text-red-700 font-medium">¿Eliminar esta sesión?</span>
              <div class="flex gap-2">
                <button
                  @click="deletingSessionId = null"
                  class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition"
                >
                  Cancelar
                </button>
                <button
                  @click="handleDelete(session.id)"
                  class="px-3 py-1 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition"
                >
                  Eliminar
                </button>
              </div>
            </div>

            <!-- Vista edición -->
            <div
              v-else
              class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm space-y-2"
            >
              <div class="flex gap-2 items-center">
                <label class="text-gray-600 w-16 shrink-0">Inicio</label>
                <input
                  v-model="editForm.startedAt"
                  type="datetime-local"
                  class="flex-1 border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
              </div>
              <div class="flex gap-2 items-center">
                <label class="text-gray-600 w-16 shrink-0">Fin</label>
                <input
                  v-model="editForm.endedAt"
                  type="datetime-local"
                  class="flex-1 border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
              </div>
              <div v-if="editError" class="text-red-500 text-xs">{{ editError }}</div>
              <div class="flex gap-2 justify-end pt-1">
                <button
                  @click="cancelEdit"
                  class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition"
                >
                  Cancelar
                </button>
                <button
                  @click="handleUpdate(session.id)"
                  :disabled="editLoading"
                  class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded transition disabled:opacity-50"
                >
                  {{ editLoading ? '...' : 'Guardar' }}
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

const showModal         = ref(false)
const loading           = ref(false)
const loadingMessage    = ref('')
const confirmingStart   = ref(false)
const confirmingStop    = ref(false)
const editingSessionId  = ref<string | null>(null)
const deletingSessionId = ref<string | null>(null)
const editLoading       = ref(false)
const editError         = ref<string | null>(null)
const editForm          = ref({ startedAt: '', endedAt: '' })

const isActive = computed(() =>
  timer.isRunning && timer.activeItemId === props.item.id
)

const totalHours = computed(() =>
  props.item.sessions
    .filter(s => s.endedAt !== null)
    .reduce((sum, s) => sum + s.durationHours, 0)
)

function closeModal() {
  showModal.value         = false
  deletingSessionId.value = null
  cancelEdit()
}

function startEdit(session: Session) {
  deletingSessionId.value = null
  editingSessionId.value  = session.id
  editError.value         = null
  editForm.value = {
    startedAt: toDatetimeLocal(session.startedAt),
    endedAt:   session.endedAt ? toDatetimeLocal(session.endedAt) : '',
  }
}

function cancelEdit() {
  editingSessionId.value = null
  editError.value        = null
  editForm.value         = { startedAt: '', endedAt: '' }
}

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

async function handleUpdate(sessionId: string) {
  editLoading.value = true
  editError.value   = null

  try {
    const { data } = await api.put(`/work-sessions/${sessionId}`, {
      startedAt: new Date(editForm.value.startedAt).toISOString(),
      endedAt:   editForm.value.endedAt
        ? new Date(editForm.value.endedAt).toISOString()
        : null,
    })

    projectStore.updateSession(props.item.id, {
      id:            data.id,
      startedAt:     data.startedAt,
      endedAt:       data.endedAt,
      durationHours: data.durationHours,
    })

    cancelEdit()
    toast.success('Sesión actualizada correctamente')

  } catch (e: any) {
    editError.value = e.response?.data?.error ?? 'Error al guardar'
    toast.error(editError.value!)
  } finally {
    editLoading.value = false
  }
}

async function handleDelete(sessionId: string) {
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