import { defineStore } from 'pinia'
import { api } from '../api/api'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    totalCents: 0,
    busy: false,
    error: '',
  }),

  getters: {
    count: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
  },

  actions: {
    async load() {
      if (this.busy) return false

      this.items = []
      this.totalCents = 0

      return this.request('get', '/api/cart')
    },

    add(productId, quantity) {
      return this.request('post', '/api/cart/items', {
        product_id: productId,
        quantity,
      })
    },

    update(itemId, quantity) {
      return this.request('patch', `/api/cart/items/${itemId}`, { quantity })
    },

    remove(itemId) {
      return this.request('delete', `/api/cart/items/${itemId}`)
    },

    async request(method, url, data) {
      if (this.busy) return false

      this.busy = true
      this.error = ''

      try {
        if (method !== 'get') {
          await api.get('/sanctum/csrf-cookie')
        }

        const { data: payload } = await api.request({ method, url, data })

        this.items = payload.data.items
        this.totalCents = payload.data.total_cents

        return true
      } catch (error) {
        const errors = error.response?.data?.errors ?? {}

        this.error = Object.values(errors).flat()[0]
          ?? 'Não foi possível acessar o carrinho. Tente novamente.'

        return false
      } finally {
        this.busy = false
      }
    },
  },
})