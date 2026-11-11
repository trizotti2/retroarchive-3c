<script setup>
import { computed, ref, watch } from 'vue'
import { formatPrice } from '../util/format'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'

const auth = useAuthStore()
const cart = useCartStore()
const quantities = ref({})

const locked = computed(() => !auth.ready || auth.pending || cart.busy)

watch(
  () => cart.items,
  (items) => {
    quantities.value = Object.fromEntries(
      items.map((item) => [item.id, item.quantity]),
    )
  },
  { immediate: true },
)
</script>

<template>
  <h1 class="mb-4">Meu carrinho</h1>

  <v-progress-linear v-if="locked" indeterminate class="mb-4" />

  <v-alert v-if="cart.error" type="error" class="mb-4">
    {{ cart.error }}
    <v-btn :disabled="locked" variant="text" @click="cart.load()">
      Recarregar carrinho
    </v-btn>
  </v-alert>

  <p v-if="!locked && !cart.error && cart.items.length === 0">
    Seu carrinho está vazio.
  </p>

  <v-card v-for="item in cart.items" :key="item.id" class="mb-4">
    <v-card-title>{{ item.name }}</v-card-title>

    <v-card-text>
      <p>Preço unitário: {{ formatPrice(item.unit_price_cents) }}</p>
      <p>Subtotal: {{ formatPrice(item.subtotal_cents) }}</p>

      <v-alert v-if="!item.available" type="warning" class="my-3">
        Item indisponível na quantidade atual.
      </v-alert>

      <v-form
        class="mt-4"
        :disabled="locked"
        @submit.prevent="cart.update(item.id, quantities[item.id])"
      >
        <v-text-field
          v-model.number="quantities[item.id]"
          label="Quantidade"
          type="number"
          min="0"
          hint="Use 0 para remover o item."
          persistent-hint
        />

        <v-btn type="submit" :disabled="locked" color="primary" class="mr-2">
          Salvar quantidade
        </v-btn>

        <v-btn
          type="button"
          :disabled="locked"
          variant="text"
          color="error"
          @click="cart.remove(item.id)"
        >
          Remover
        </v-btn>
      </v-form>
    </v-card-text>
  </v-card>

  <p v-if="cart.items.length" class="text-h6">
    Total: {{ formatPrice(cart.totalCents) }}
  </p>

  <v-btn :to="{ name: 'catalog' }" variant="text" class="mt-4">
    Voltar ao catálogo
  </v-btn>
</template>