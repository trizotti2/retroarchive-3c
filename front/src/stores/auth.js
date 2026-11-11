import { defineStore } from 'pinia'
import { api } from '../api/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    ready: false,
    pending: false,
    error: '',
    cartAdjustments: [],
  }),

  actions: {
    async restoreSession() {
      try {
        const { data: payload } = await api.get('/api/auth/me')
        this.user = payload.data
      } catch (error) {
        if (error.response?.status === 401) {
          this.user = null
        } else {
          this.error = 'Não foi possível verificar a sessão.'
        }
      } finally {
        this.ready = true
      }
    },

    async authenticate(action, fields) {
      this.pending = true
      this.error = ''

      try {
        await api.get('/sanctum/csrf-cookie')
        const { data: payload } = await api.post(`/api/auth/${action}`, fields)
        this.user = payload.data
        this.cartAdjustments = payload.cart_adjustments ?? []
      } finally {
        this.pending = false
      }
    },

    async logout() {
      if (this.pending) return

      this.pending = true
      this.error = ''

      try {
        await api.get('/sanctum/csrf-cookie')
        await api.post('/api/auth/logout')
        this.user = null
        this.cartAdjustments = []
      } catch (error) {
        if (error.response?.status === 401) {
          this.user = null
          this.cartAdjustments = []
        } else {
          this.error = 'Não foi possível sair. Tente novamente.'
        }
      } finally {
        this.pending = false
      }
    },
  },
})