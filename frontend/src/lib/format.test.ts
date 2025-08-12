import { describe, it, expect } from 'vitest'
import { displayOrderCode } from './format'

describe('displayOrderCode', () => {
  it('formats like 123/24', () => {
    expect(displayOrderCode('123/24')).toBe('123/24')
    expect(displayOrderCode('123-24')).toBe('123/24')
    expect(displayOrderCode('123\\24')).toBe('123/24')
  })
  it('formats from object with props', () => {
    expect(displayOrderCode({ id: 5 })).toBe('5')
    expect(displayOrderCode({ legacy_number: '77/24' })).toBe('77/24')
  })
})
