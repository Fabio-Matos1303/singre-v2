import { defineStore } from 'pinia'

export type ToastKind = 'success' | 'error' | 'info'
export interface ToastItem { id: number; message: string; kind: ToastKind; timeoutMs?: number }

export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: [] as ToastItem[],
    nextId: 1,
  }),
  actions: {
    push(message: string, kind: ToastKind = 'info', timeoutMs = 3500) {
      const id = this.nextId++
      const item: ToastItem = { id, message, kind, timeoutMs }
      this.toasts.push(item)
      if (timeoutMs && timeoutMs > 0) {
        setTimeout(() => this.remove(id), timeoutMs)
      }
    },
    success(message: string) { this.push(message, 'success') },
    error(message: string) { this.push(message, 'error', 5000) },
    info(message: string) { this.push(message, 'info') },
    remove(id: number) {
      this.toasts = this.toasts.filter(t => t.id !== id)
    },
    clear() { this.toasts = [] },
  },
})
