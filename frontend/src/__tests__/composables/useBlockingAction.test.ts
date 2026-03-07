import { describe, it, expect } from 'vitest'
import { useBlockingAction } from '@/composables/useBlockingAction'

describe('useBlockingAction', () => {
  it('starts with loading false and empty message', () => {
    const { loading, loadingMessage } = useBlockingAction()

    expect(loading.value).toBe(false)
    expect(loadingMessage.value).toBe('')
  })

  it('sets loading and message during action', async () => {
    const { loading, loadingMessage, run } = useBlockingAction()
    let capturedLoading = false
    let capturedMessage = ''

    await run('Saving...', async () => {
      capturedLoading = loading.value
      capturedMessage = loadingMessage.value
    })

    expect(capturedLoading).toBe(true)
    expect(capturedMessage).toBe('Saving...')
    expect(loading.value).toBe(false)
    expect(loadingMessage.value).toBe('')
  })

  it('resets loading on error', async () => {
    const { loading, loadingMessage, run } = useBlockingAction()

    try {
      await run('Failing...', async () => {
        throw new Error('fail')
      })
    } catch {
      // expected
    }

    expect(loading.value).toBe(false)
    expect(loadingMessage.value).toBe('')
  })

  it('returns action result', async () => {
    const { run } = useBlockingAction()

    const result = await run('Loading...', async () => 42)

    expect(result).toBe(42)
  })
})
