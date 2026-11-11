import { defineStore } from 'pinia'
import { api } from '../api/api'

export const useCatalogStore = defineStore('catalog', {
  state: () => ({
    categories: [],
    products: [],
    filters: { search: '', category: '', sort: 'latest' },
    page: 1,
    lastPage: 1,
    loading: false,
    error: '',
  }),

  actions: {
    async loadProducts(page = this.page) {
      if (this.loading) return

      this.loading = true
      this.error = ''
      this.page = page

      try {
        if (this.categories.length === 0) {
          const { data: payload } = await api.get('/api/categories')
          this.categories = payload.data
        }

        const { search, category, sort } = this.filters
        const params = { page, sort }

        if (search) params.search = search
        if (category) params.category = category

        const { data: payload } = await api.get('/api/products', { params })

        this.products = payload.data
        this.lastPage = payload.meta.last_page
      } catch {
        this.error = 'Não foi possível carregar a vitrine. Tente novamente.'
      } finally {
        this.loading = false
      }
    },
  },
})