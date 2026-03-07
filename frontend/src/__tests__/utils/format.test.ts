import { describe, it, expect } from 'vitest'
import { formatHours } from '@/utils/format'

describe('formatHours', () => {
  it('formats whole hours', () => {
    expect(formatHours(3)).toBe('3h')
  })

  it('formats hours with minutes', () => {
    expect(formatHours(1.5)).toBe('1h 30m')
  })

  it('formats zero hours', () => {
    expect(formatHours(0)).toBe('0h')
  })

  it('formats fractional hours', () => {
    expect(formatHours(2.25)).toBe('2h 15m')
  })

  it('formats less than one hour', () => {
    expect(formatHours(0.5)).toBe('0h 30m')
  })
})
