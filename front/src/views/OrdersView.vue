<script setup>
import { onMounted, ref } from 'vue'
import StatusChip from '../components/StatusChip.vue'
import api, { failure } from '../api/api'
import { date, money } from '../util/format'

const orders = ref([])
const meta = ref(null)
const loading = ref(true)
const error = ref('')

async function load(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/orders', { params: { page } })
    orders.value = data.data
    meta.value = data.meta
  } catch (requestError) {
    error.value = failure(requestError, 'Não foi possível carregar os pedidos.')
  } finally {
    loading.value = false
  }
}

onMounted(() => load())
</script>

<template>
  <div class="page-heading">
    <p class="eyebrow"><span class="signal-dot"></span> SUA CONTA</p>
    <h1 class="page-title">Meus pedidos</h1>
  </div>

  <AppProgress v-if="loading" />
  <AppAlert v-else-if="error" type="error">
    {{ error }}
    <AppButton variant="text" @click="load(meta?.current_page ?? 1)">Tentar novamente</AppButton>
  </AppAlert>
  <div v-else-if="orders.length === 0" class="panel empty-state">
    <AppIcon icon="mdi-package-variant" size="38" color="primary" />
    <h2>Você ainda não fez pedidos</h2>
    <AppButton :to="{ name: 'catalog' }" color="primary">Explorar catálogo</AppButton>
  </div>
  <div v-else class="stack">
    <article v-for="order in orders" :key="order.id" class="panel order-line">
      <div><h2>Pedido #{{ order.id }}</h2><p class="muted">{{ date(order.created_at) }}</p></div>
      <StatusChip :status="order.status" />
      <strong>{{ money(order.total_cents) }}</strong>
      <AppButton :to="{ name: 'order', params: { id: order.id } }" variant="tonal" append-icon="mdi-arrow-right">Ver pedido</AppButton>
    </article>

    <AppPagination
      v-if="meta?.last_page > 1"
      :model-value="meta.current_page"
      :length="meta.last_page"
      @update:model-value="load"
    />
  </div>
</template>
