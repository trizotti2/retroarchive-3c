<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProductArt from '../components/ProductArt.vue'
import api, { failure } from '../api/api'
import { categoryInfo, money } from '../util/format'

const route = useRoute()
const router = useRouter()
const categories = ref([])
const products = ref([])
const pagination = ref(null)
const search = ref('')
const loading = ref(true)
const error = ref('')
const categoriesError = ref('')
const shelf = ref(null)
const sortOptions = [
  { title: 'Mais recentes', value: 'latest' },
  { title: 'Menor preço', value: 'price_asc' },
  { title: 'Maior preço', value: 'price_desc' },
]

const category = computed(() => typeof route.query.category === 'string' ? route.query.category : '')
const sort = computed(() => sortOptions.some((option) => option.value === route.query.sort) ? route.query.sort : 'latest')
const page = computed(() => Math.max(1, Number(route.query.page) || 1))
const categoryName = computed(() => categories.value.find((item) => item.slug === category.value)?.name)

onMounted(async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = [...data.data].sort((a, b) =>
      (categoryInfo[a.slug]?.number ?? '99').localeCompare(categoryInfo[b.slug]?.number ?? '99'),
    )
  } catch (requestError) {
    categoriesError.value = failure(requestError, 'Não foi possível carregar as categorias.')
  }
})

watch(() => route.fullPath, async (_, __, onCleanup) => {
  if (route.name !== 'catalog') return
  const controller = new AbortController()
  onCleanup(() => controller.abort())

  search.value = typeof route.query.search === 'string' ? route.query.search : ''
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/products', {
      signal: controller.signal,
      params: {
        search: search.value || undefined,
        category: category.value || undefined,
        sort: sort.value,
        page: page.value,
        per_page: 12,
      },
    })
    products.value = data.data
    pagination.value = data.meta
  } catch (requestError) {
    if (!controller.signal.aborted) error.value = failure(requestError, 'Não foi possível carregar o catálogo.')
  } finally {
    if (!controller.signal.aborted) loading.value = false
  }
}, { immediate: true })

function setFilters(filters) {
  return router.replace({ name: 'catalog', query: { ...route.query, page: undefined, ...filters } })
}

async function goToShelf(slug) {
  await setFilters({ category: slug || undefined })
  await nextTick()
  shelf.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function goToPage(nextPage) {
  setFilters({ page: nextPage > 1 ? nextPage : undefined })
  shelf.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function submitSearch() {
  setFilters({ search: search.value.trim() || undefined })
}
</script>

<template>
  <section class="hero" aria-labelledby="hero-title">
    <div class="hero-copy">
      <p class="eyebrow hero-kicker"><span class="signal-dot"></span> ARQUIVO DE GAMES <span class="hero-divider"></span> VOL. 01</p>
      <h1 id="hero-title">Volte à fase<br /><em>clássica.</em></h1>
      <p>Consoles, jogos e acessórios para revisitar as gerações que marcaram o seu jeito de jogar.</p>
      <div class="actions hero-actions">
        <AppButton color="primary" size="large" append-icon="mdi-arrow-right" @click="goToShelf('')">
          Explorar catálogo
        </AppButton>
        <AppButton variant="text" size="large" class="hero-link" @click="goToShelf('consoles')">
          Ver consoles
        </AppButton>
      </div>
    </div>
    <div class="hero-art" aria-hidden="true">
      <img src="/media/consoles.svg" alt="" width="640" height="400" fetchpriority="high" />
      <span class="hero-art-label mono"><span class="accent">RA/01</span> · ILUSTRAÇÃO</span>
    </div>
    <span class="hero-index mono" aria-hidden="true">01 — CATÁLOGO</span>
  </section>

  <section class="category-grid" aria-label="Filtrar por categoria" v-if="categories.length">
    <button
      v-for="(item, index) in categories"
      :key="item.id"
      class="category-tile"
      :class="{ selected: category === item.slug }"
      :aria-pressed="category === item.slug"
      @click="goToShelf(item.slug)"
    >
      <span class="category-icon"><AppIcon :icon="categoryInfo[item.slug]?.icon ?? 'mdi-tag-outline'" size="25" /></span>
      <span class="category-text"><strong>{{ item.name }}</strong><small>{{ categoryInfo[item.slug]?.tagline ?? 'Ver produtos' }}</small></span>
      <span class="category-number mono">{{ categoryInfo[item.slug]?.number ?? String(index + 1).padStart(2, '0') }}</span>
      <AppIcon icon="mdi-arrow-top-right" class="category-arrow" size="19" />
    </button>
  </section>

  <section ref="shelf" class="shelf" aria-labelledby="catalog-heading">
    <div class="section-heading">
      <div>
        <p class="eyebrow"><span class="signal-dot"></span> NA PRATELEIRA</p>
        <h2 id="catalog-heading">{{ categoryName || 'Catálogo retrô' }}</h2>
      </div>
      <p v-if="pagination && !loading && !error" class="muted mono">{{ pagination.total }} {{ pagination.total === 1 ? 'ITEM' : 'ITENS' }}</p>
    </div>

    <AppAlert v-if="categoriesError" type="warning" class="mb-4">{{ categoriesError }}</AppAlert>

    <div class="catalog-controls">
      <form class="search-form" role="search" @submit.prevent="submitSearch">
        <AppField
          v-model="search"
          label="Buscar no catálogo"
          placeholder="Ex.: Dreamcast"
          prepend-inner-icon="mdi-magnify"
          maxlength="100"
          hide-details
        />
        <AppButton type="submit" variant="tonal">Buscar</AppButton>
      </form>
      <AppSelect
        :model-value="sort"
        :items="sortOptions"
        label="Ordenação"
        @update:model-value="setFilters({ sort: $event === 'latest' ? undefined : $event })"
      />
    </div>

    <div v-if="category || route.query.search" class="actions mb-4">
      <AppChip v-if="category" closable color="secondary" @close="setFilters({ category: undefined })">{{ categoryName || category }}</AppChip>
      <AppChip v-if="route.query.search" closable color="secondary" @close="setFilters({ search: undefined })">Busca: {{ route.query.search }}</AppChip>
    </div>

    <div v-if="loading" class="product-grid" aria-label="Carregando catálogo">
      <div v-for="index in 3" :key="index" class="app-skeleton" aria-hidden="true"></div>
    </div>

    <AppAlert v-else-if="error" type="error">{{ error }}</AppAlert>

    <div v-else-if="products.length === 0" class="empty-state panel">
      <AppIcon icon="mdi-magnify" size="34" color="primary" />
      <h3>Nenhum produto encontrado</h3>
      <p>Experimente outro termo ou volte a ver todos os produtos.</p>
      <AppButton variant="tonal" @click="setFilters({ search: undefined, category: undefined })">Limpar filtros</AppButton>
    </div>

    <div v-else class="product-grid">
      <article v-for="product in products" :key="product.id" class="product-card">
        <RouterLink :to="{ name: 'product', params: { id: product.id } }" class="product-image" :aria-label="`Ver ${product.name}`">
          <ProductArt :src="product.image_url" :category="product.category?.slug" />
          <span class="art-corner mono">{{ product.category?.name || 'ARQUIVO' }}</span>
          <span v-if="!product.available" class="stock-flag">Esgotado</span>
        </RouterLink>
        <div class="product-card-body">
          <h3>{{ product.name }}</h3>
          <p>{{ product.description }}</p>
          <div class="product-card-end">
            <strong>{{ money(product.price_cents) }}</strong>
            <AppButton
              :to="{ name: 'product', params: { id: product.id } }"
              :aria-label="`Ver produto: ${product.name}`"
              variant="tonal"
              size="small"
              append-icon="mdi-arrow-right"
            >Ver produto</AppButton>
          </div>
        </div>
      </article>
    </div>

    <AppPagination
      v-if="!loading && !error && pagination?.last_page > 1"
      :model-value="pagination.current_page"
      :length="pagination.last_page"
      class="mt-7"
      @update:model-value="goToPage"
    />
  </section>
</template>
