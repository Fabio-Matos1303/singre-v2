export function displayOrderCode(rawInput: any): string {
  const raw = (rawInput && typeof rawInput === 'object')
    ? (rawInput.code || rawInput.legacy_number || rawInput.id)
    : rawInput
  if (!raw || typeof raw !== 'string') return String(raw)
  const m = raw.match(/^(\d{1,})([\/\\-])(\d{2})$/)
  if (m) {
    return `${parseInt(m[1], 10)}/${m[3]}`
  }
  return raw
}
