import { defineStore } from 'pinia'
import api, { failure, mutate } from '../api/api'

let restoring

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null, ready: false, pending: false, error: '', cartAdjustments: [],
  }),

  actions: {
    restoreSession() {
      if (this.ready) return Promise.resolve()
      if (restoring) return restoring

      restoring = api.get('/auth/me').then(({ data }) => {
        this.user = data.data
      }).catch((error) => {
        if (error.response?.status !== 401) {
          this.error = failure(error, 'Não foi possível verificar sua sessão.')
        }
      }).finally(() => {
        this.ready = true
        restoring = undefined
      })

      return restoring
    },

    async authenticate(action, fields) {
      this.pending = true
      this.error = ''
      try {
        const payload = await mutate('post', `/auth/${action}`, fields)
        this.cartAdjustments = payload.cart_adjustments ?? []
        this.user = payload.data
      } finally {
        this.pending = false
      }
    },

    async logout() {
      this.pending = true
      this.error = ''
      try {
        await mutate('post', '/auth/logout')
        this.clearUser()
      } catch (error) {
        if (error.response?.status === 401) this.clearUser()
        else this.error = failure(error, 'Não foi possível sair. Tente novamente.')
      } finally {
        this.pending = false
      }
    },

    clearUser() {
      this.user = null
      this.cartAdjustments = []
    },
  },
})
