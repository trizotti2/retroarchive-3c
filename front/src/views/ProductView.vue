<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../api/api'
import { formatPrice } from '../util/format'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'

const props = defineProps({
  id: { type: String, required: true },
})

const auth = useAuthStore()
const cart = useCartStore()
const quantity = ref(1)
const addStatus = ref(null)

async function addToCart() {
  if (!auth.ready || auth.pending || cart.busy) return

  addStatus.value = null
  const added = await cart.add(product.value.id, quantity.value)
  addStatus.value = added ? 'success' : 'error'
}

const product = ref(null)
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const { data: payload } = await api.get(
      `/api/products/${encodeURIComponent(props.id)}`,
    )

    product.value = payload.data
  } catch (requestError) {
    error.value = requestError.response?.status === 404
      ? 'Produto não encontrado.'
      : 'Não foi possível carregar o produto.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <v-btn :to="{ name: 'catalog' }" variant="text" class="mb-4">
    Voltar ao catálogo
  </v-btn>

  <v-progress-linear v-if="loading" indeterminate />
  <v-alert v-else-if="error" type="error">{{ error }}</v-alert>

  <v-card v-else-if="product">
    <v-img v-if="product.image_url" :src="product.image_url" :alt="product.name" height="280" />

    <v-card-title>{{ product.name }}</v-card-title>
    <v-card-subtitle>{{ product.category.name }}</v-card-subtitle>

    <v-card-text>
      <v-card-text v-if="product.available">
  <v-text-field
    v-model.number="quantity"
    label="Quantidade"
    type="number"
    min="1"
    :max="product.stock"
    :disabled="cart.busy"
  />

  <v-btn
    color="primary"
    :disabled="
      !auth.ready || auth.pending || cart.busy ||
      !Number.isInteger(quantity) || quantity < 1 || quantity > product.stock
    "
    @click="addToCart"
  >
    Adicionar ao carrinho
  </v-btn>

  <v-alert v-if="addStatus" :type="addStatus" class="mt-4">
    {{ addStatus === 'success' ? 'Produto adicionado ao carrinho.' : cart.error }}
  </v-alert>
</v-card-text>

      <p class="mb-3">{{ product.description }}</p>
      <p class="text-h6 mb-3">{{ formatPrice(product.price_cents) }}</p>
      <v-chip :color="product.available ? 'success' : 'warning'">
        {{ product.available ? `${product.stock} em estoque` : 'Esgotado' }}
      </v-chip>
    </v-card-text>
  </v-card>
</template>