import { defineStore } from 'pinia'
import api from '../lib/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') as string | null,
    loading: false as boolean,
    error: '' as string,
  }),
  actions: {
    async login(email: string, password: string) {
      this.loading = true
      this.error = ''
      try {
        const { data } = await api.post('/api/auth/login', { email, password })
        this.token = data.token
        localStorage.setItem('auth_token', data.token)
      } catch (e: any) {
        this.error = e?.response?.data?.message || 'Falha no login'
        throw e
      } finally {
        this.loading = false
      }
    },
    async logout() {
      try { await api.post('/api/auth/logout') } catch {}
      this.token = null
      localStorage.removeItem('auth_token')
    },
  },
})
