import { ref, type Ref, readonly } from 'vue'

interface BlockingAction {
  loading:        Readonly<Ref<boolean>>
  loadingMessage: Readonly<Ref<string>>
  run: <T>(message: string, action: () => Promise<T>) => Promise<T | undefined>
}

export function useBlockingAction(): BlockingAction {
  const loading        = ref(false)
  const loadingMessage = ref('')

  async function run<T>(message: string, action: () => Promise<T>): Promise<T | undefined> {
    loading.value        = true
    loadingMessage.value = message

    try {
      return await action()
    } finally {
      loading.value        = false
      loadingMessage.value = ''
    }
  }

  return {
    loading:        readonly(loading),
    loadingMessage: readonly(loadingMessage),
    run,
  }
}
