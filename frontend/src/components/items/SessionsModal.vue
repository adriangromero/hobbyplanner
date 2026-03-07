<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="!loading && close()"
    >
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">

        <BlockingOverlay :active="loading" :message="loadingMessage" mode="local" />

        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">
            Sesiones — {{ item.name }}
          </h3>
          <button
            @click="!loading && close()"
            :class="{ 'opacity-50 pointer-events-none': loading }"
            class="text-gray-400 hover:text-gray-700 transition-colors"
          >
            ✕
          </button>
        </div>

        <!-- Loading inicial -->
        <div v-if="fetching" class="py-8 flex justify-center">
          <div class="w-8 h-8 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin" />
        </div>

        <template v-else>
          <div
            v-if="sessions.length === 0"
            class="text-gray-400 text-sm py-4 text-center"
          >
            No hay sesiones registradas
          </div>

          <div
            v-else
            class="space-y-2 max-h-80 overflow-y-auto"
            :class="{ 'pointer-events-none opacity-70': loading }"
          >
            <div v-for="session in sessions" :key="session.id">

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
                  <span class="font-medium text-gray-800">
                    {{ formatHours(session.durationHours) }}
                  </span>

                  <button
                    @click="startEditSession(session)"
                    :disabled="loading"
                    class="text-blue-400 hover:text-blue-600 transition-colors disabled:opacity-50"
                  >
                    ✏️
                  </button>

                  <button
                    @click="deletingSessionId = session.id"
                    :disabled="loading"
                    class="text-red-400 hover:text-red-600 transition-colors disabled:opacity-50"
                  >
                    🗑️
                  </button>
                </div>
              </div>

              <!-- Confirmar eliminar -->
              <div
                v-else-if="deletingSessionId === session.id"
                class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm flex justify-between items-center"
              >
                <span class="text-red-700 font-medium">
                  ¿Eliminar esta sesión?
                </span>

                <div class="flex gap-2">
                  <button
                    @click="deletingSessionId = null"
                    :disabled="loading"
                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 disabled:opacity-50"
                  >
                    Cancelar
                  </button>

                  <button
                    @click="handleDeleteSession(session)"
                    :disabled="loading"
                    class="px-3 py-1 text-sm bg-red-600 hover:bg-red-700 text-white rounded disabled:opacity-50"
                  >
                    {{ loading ? '...' : 'Eliminar' }}
                  </button>
                </div>
              </div>

              <!-- Editar -->
              <div
                v-else
                class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm space-y-2"
              >
                <div class="flex gap-2 items-center">
                  <label class="text-gray-600 w-16 shrink-0">Inicio</label>
                  <input
                    v-model="editSessionForm.startedAt"
                    type="datetime-local"
                    :disabled="loading"
                    class="flex-1 border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50"
                  />
                </div>

                <div class="flex gap-2 items-center">
                  <label class="text-gray-600 w-16 shrink-0">Fin</label>
                  <input
                    v-model="editSessionForm.endedAt"
                    type="datetime-local"
                    :disabled="loading"
                    class="flex-1 border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50"
                  />
                </div>

                <div v-if="editSessionError" class="text-red-500 text-xs">
                  {{ editSessionError }}
                </div>

                <div class="flex gap-2 justify-end pt-1">
                  <button
                    @click="cancelEditSession"
                    :disabled="loading"
                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 disabled:opacity-50"
                  >
                    Cancelar
                  </button>

                  <button
                    @click="handleUpdateSession(session)"
                    :disabled="loading"
                    class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded disabled:opacity-50"
                  >
                    {{ loading ? '...' : 'Guardar' }}
                  </button>
                </div>
              </div>

            </div>
          </div>

          <div
            v-if="sessions.length > 0"
            class="mt-4 pt-4 border-t flex justify-end"
          >
            <span class="text-sm text-gray-500">
              Total: <strong>{{ formatHours(totalHours) }}</strong>
            </span>
          </div>
        </template>

      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useProjectStore } from '@/stores/projectStore'
import { useBlockingAction } from '@/composables/useBlockingAction'
import { useToast } from '@/composables/useToast'
import { formatDate, formatHours } from '@/utils/format'
import BlockingOverlay from '@/components/ui/BlockingOverlay.vue'
import api from '@/api/axios'

interface Session {
  id: string
  startedAt: string
  endedAt: string | null
  durationHours: number
}

interface Item {
  id: string
  name: string
  totalSessions: number
  totalHours: number
}

const props = defineProps<{ modelValue: boolean; item: Item }>()
const emit  = defineEmits<{ 'update:modelValue': [value: boolean] }>()

const projectStore                       = useProjectStore()
const toast                              = useToast()
const { loading, loadingMessage, run }   = useBlockingAction()

const sessions          = ref<Session[]>([])
const fetching          = ref(false)
const editingSessionId  = ref<string | null>(null)
const deletingSessionId = ref<string | null>(null)
const editSessionError  = ref<string | null>(null)
const editSessionForm   = ref({ startedAt: '', endedAt: '' })

const totalHours = computed(() =>
  sessions.value
    .filter(s => s.endedAt !== null)
    .reduce((sum, s) => sum + s.durationHours, 0)
)

watch(() => props.modelValue, async (open) => {
  if (!open) return

  fetching.value = true
  try {
    const { data } = await api.get(`/items/${props.item.id}/sessions`)
    sessions.value = data.sessions
  } catch {
    toast.error('Error al cargar las sesiones')
  } finally {
    fetching.value = false
  }
})

function close() {
  if (loading.value) return

  emit('update:modelValue', false)
  sessions.value = []
  deletingSessionId.value = null
  cancelEditSession()
}

function startEditSession(session: Session) {
  if (loading.value) return

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

async function handleUpdateSession(session: Session) {
  editSessionError.value = null

  await run('Guardando sesión...', async () => {
    try {
      const { data } = await api.put(`/work-sessions/${session.id}`, {
        startedAt: new Date(editSessionForm.value.startedAt).toISOString(),
        endedAt:   editSessionForm.value.endedAt
          ? new Date(editSessionForm.value.endedAt).toISOString()
          : null,
      })

      const oldHours = session.endedAt ? session.durationHours : 0
      const newHours = data.durationHours ?? 0
      projectStore.adjustItemTotalHours(props.item.id, newHours - oldHours)

      const index = sessions.value.findIndex(s => s.id === session.id)
      if (index !== -1) {
        sessions.value[index] = {
          id: data.id,
          startedAt: data.startedAt,
          endedAt: data.endedAt,
          durationHours: data.durationHours,
        }
      }

      cancelEditSession()
      toast.success('Sesión actualizada correctamente')

    } catch (e: any) {
      editSessionError.value = e.response?.data?.error ?? 'Error al guardar'
    }
  })
}

async function handleDeleteSession(session: Session) {
  await run('Eliminando sesión...', async () => {
    try {
      await api.delete(`/work-sessions/${session.id}`)

      const hoursToRemove = session.endedAt ? session.durationHours : 0
      projectStore.adjustItemTotalHours(props.item.id, -hoursToRemove, -1)

      sessions.value = sessions.value.filter(s => s.id !== session.id)
      deletingSessionId.value = null

      toast.success('Sesión eliminada correctamente')
    } catch {
      toast.error('Error al eliminar la sesión')
    }
  })
}

function toDatetimeLocal(isoString: string): string {
  const date = new Date(isoString)
  const offset = date.getTimezoneOffset()
  const local = new Date(date.getTime() - offset * 60000)
  return local.toISOString().slice(0, 16)
}
</script>
