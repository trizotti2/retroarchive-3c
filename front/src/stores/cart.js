import { defineStore } from 'pinia'
import api, { failure, mutate } from '../api/api'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [], totalCents: 0, shippingCents: 0, checkoutTotalCents: 0,
    busy: false, error: '', revision: 0,
  }),

  getters: {
    count: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    hasUnavailable: (state) => state.items.some((item) => !item.available),
  },

  actions: {
    setCart(cart) {
      this.items = cart.items
      this.totalCents = cart.total_cents
      this.shippingCents = cart.shipping_cents
      this.checkoutTotalCents = cart.checkout_total_cents
    },

    async load(reset = false) {
      if (reset) {
        this.setCart({ items: [], total_cents: 0, shipping_cents: 0, checkout_total_cents: 0 })
      }

      return this.request(async () => (await api.get('/cart')).data, reset)
    },

    add(productId, quantity) {
      return this.request(() => mutate('post', '/cart/items', { product_id: productId, quantity }))
    },

    update(id, quantity) {
      return this.request(() => mutate('patch', `/cart/items/${id}`, { quantity }))
    },

    remove(id) {
      return this.request(() => mutate('delete', `/cart/items/${id}`))
    },

    async request(send, replace = false) {
      if (this.busy && !replace) return false

      const revision = ++this.revision
      this.busy = true
      this.error = ''

      try {
        const payload = await send()
        if (revision !== this.revision) return false
        this.setCart(payload.data)
        return true
      } catch (error) {
        if (revision === this.revision) this.error = failure(error, 'Não foi possível carregar o carrinho.')
        return false
      } finally {
        if (revision === this.revision) this.busy = false
      }
    },
  },
})
