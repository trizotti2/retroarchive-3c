<script setup>
import { onMounted, ref } from 'vue'
import ProductArt from '../components/ProductArt.vue'
import api, { failure } from '../api/api'
import { money } from '../util/format'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'

const props = defineProps({ id: { type: String, required: true } })
const auth = useAuthStore()
const cart = useCartStore()
const product = ref(null)
const quantity = ref(1)
const loading = ref(true)
const error = ref('')
const added = ref(null)

onMounted(async () => {
  try {
    const { data } = await api.get(`/products/${props.id}`)
    product.value = data.data
  } catch (requestError) {
    error.value = requestError.response?.status === 404
      ? 'Produto não encontrado.'
      : failure(requestError, 'Não foi possível carregar este produto.')
  } finally {
    loading.value = false
  }
})

async function add() {
  if (cart.busy || auth.pending || !auth.ready) return
  added.value = await cart.add(product.value.id, Number(quantity.value))
}
</script>

<template>
  <AppButton :to="{ name: 'catalog' }" variant="text" prepend-icon="mdi-arrow-left" class="mb-4">Catálogo</AppButton>
  <AppProgress v-if="loading" />
  <AppAlert v-else-if="error" type="error">{{ error }}</AppAlert>

  <article v-else-if="product" class="detail-grid">
    <div class="detail-art panel">
      <ProductArt :src="product.image_url" :category="product.category?.slug" eager />
    </div>
    <div class="detail-copy">
      <p class="eyebrow"><span class="signal-dot"></span> {{ product.category?.name }}</p>
      <h1 class="page-title">{{ product.name }}</h1>
      <p class="detail-description">{{ product.description }}</p>
      <p class="detail-price accent">{{ money(product.price_cents) }}</p>
      <AppChip :color="product.available ? 'success' : 'warning'">
        {{ product.available ? `${product.stock} em estoque` : 'Esgotado' }}
      </AppChip>

      <form v-if="product.available" class="detail-buy" @submit.prevent="add">
        <AppField v-model.number="quantity" label="Quantidade" type="number" min="1" :max="product.stock" required hide-details />
        <AppButton
          type="submit"
          color="primary"
          prepend-icon="mdi-cart-plus"
          size="large"
          :loading="cart.busy"
          :disabled="!auth.ready || auth.pending || !Number.isInteger(Number(quantity)) || Number(quantity) < 1 || Number(quantity) > product.stock"
        >Adicionar ao carrinho</AppButton>
      </form>

      <AppAlert v-if="added !== null" :type="added ? 'success' : 'error'" class="mt-4">
        {{ added ? 'Produto adicionado ao carrinho.' : cart.error }}
        <AppButton v-if="added" :to="{ name: 'cart' }" size="small" variant="text">Ver carrinho</AppButton>
      </AppAlert>
    </div>
  </article>
</template>
