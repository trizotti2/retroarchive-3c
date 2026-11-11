<script setup>
import { computed, onMounted, reactive } from 'vue'
import { formatPrice } from '../util/format'
import { useCatalogStore } from '../stores/catalog'

const catalog = useCatalogStore()
const form = reactive({ ...catalog.filters })

const categoryOptions = computed(() => [
  { name: 'Todas as categorias', slug: '' },
  ...catalog.categories,
])

const sortOptions = [
  { title: 'Mais recentes', value: 'latest' },
  { title: 'Menor preço', value: 'price_asc' },
  { title: 'Maior preço', value: 'price_desc' },
]

function applyFilters() {
  if (catalog.loading) return

  catalog.filters = { ...form, search: form.search.trim() }
  catalog.loadProducts(1)
}

onMounted(() => catalog.loadProducts())
</script>

<template>
  <h1 class="mb-4">Catálogo retrô</h1>

  <v-form class="mb-6" :disabled="catalog.loading" @submit.prevent="applyFilters">
    <v-row>
      <v-col cols="12" md="4">
        <v-text-field
          v-model="form.search"
          label="Buscar pelo nome"
          maxlength="100"
          hide-details
        />
      </v-col>

      <v-col cols="12" md="4">
        <v-select
          v-model="form.category"
          :items="categoryOptions"
          item-title="name"
          item-value="slug"
          label="Categoria"
          hide-details
        />
      </v-col>

      <v-col cols="12" md="4">
        <v-select
          v-model="form.sort"
          :items="sortOptions"
          label="Ordenação"
          hide-details
        />
      </v-col>
    </v-row>

    <v-btn type="submit" color="primary" class="mt-3" :loading="catalog.loading">
      Aplicar filtros
    </v-btn>
  </v-form>

  <v-progress-linear v-if="catalog.loading" indeterminate />

  <v-alert v-else-if="catalog.error" type="error">
    {{ catalog.error }}
  </v-alert>

  <template v-else>
    <p v-if="catalog.products.length === 0">Nenhum produto encontrado.</p>

    <v-row v-else>
      <v-col v-for="product in catalog.products" :key="product.id" cols="12" sm="6" md="4">
        <v-card height="100%">
          <v-img v-if="product.image_url" :src="product.image_url" :alt="product.name" height="160" cover />

          <v-card-title>{{ product.name }}</v-card-title>
          <v-card-subtitle>{{ product.category.name }}</v-card-subtitle>

          <v-card-text>
            <p class="mb-2">{{ formatPrice(product.price_cents) }}</p>
            <v-chip :color="product.available ? 'success' : 'warning'">
              {{ product.available ? 'Disponível' : 'Esgotado' }}
            </v-chip>
          </v-card-text>

          <v-card-actions>
            <v-btn :to="{ name: 'product', params: { id: product.id } }">
              Ver produto
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <v-pagination
      v-if="catalog.lastPage > 1"
      :model-value="catalog.page"
      :length="catalog.lastPage"
      :total-visible="5"
      class="mt-4"
      @update:model-value="catalog.loadProducts($event)"
    />
  </template>
</template>