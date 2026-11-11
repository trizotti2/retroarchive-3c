<script setup>
import { computed, reactive, watch } from 'vue'
import ProductArt from '../components/ProductArt.vue'
import { money } from '../util/format'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'

const auth = useAuthStore()
const cart = useCartStore()
const quantities = reactive({})
const locked = computed(() => !auth.ready || auth.pending || cart.busy)

watch(() => cart.items, (items) => {
  for (const id of Object.keys(quantities)) delete quantities[id]
  for (const item of items) quantities[item.id] = item.quantity
}, { immediate: true })
</script>

<template>
  <div class="page-heading">
    <p class="eyebrow"><span class="signal-dot"></span> SEU INVENTÁRIO</p>
    <h1 class="page-title">Meu carrinho</h1>
  </div>

  <AppProgress v-if="locked" class="mb-4" />
  <AppAlert v-if="cart.error" type="error" class="mb-5">
    {{ cart.error }}
    <AppButton variant="text" size="small" :disabled="locked" @click="cart.load()">Atualizar carrinho</AppButton>
  </AppAlert>

  <div v-if="!locked && !cart.error && !cart.items.length" class="empty-state panel">
    <AppIcon icon="mdi-cart-outline" size="40" color="primary" />
    <h2>Seu carrinho está vazio</h2>
    <p>Explore o arquivo e encontre a próxima peça para sua coleção.</p>
    <AppButton :to="{ name: 'catalog' }" color="primary">Explorar catálogo</AppButton>
  </div>

  <div v-if="cart.items.length" class="commerce-grid">
    <div class="stack">
      <article v-for="item in cart.items" :key="item.id" class="panel cart-line">
        <div class="cart-thumb">
          <ProductArt :src="item.image_url" />
        </div>
        <div class="cart-item-body">
          <h2>{{ item.name }}</h2>
          <p class="muted">{{ money(item.unit_price_cents) }} por unidade</p>
          <AppChip v-if="!item.available" color="warning" class="mt-2">
            Quantidade indisponível
          </AppChip>

          <form class="actions line-actions" @submit.prevent="cart.update(item.id, quantities[item.id])">
            <AppField
              v-model.number="quantities[item.id]"
              :label="`Quantidade de ${item.name}`"
              type="number"
              min="0"
              step="1"
              required
              hide-details
              :disabled="locked"
            />
            <AppButton type="submit" variant="tonal" size="small" :disabled="locked">Salvar</AppButton>
            <AppButton type="button" variant="text" color="error" size="small" :disabled="locked" @click="cart.remove(item.id)">
              Remover
            </AppButton>
          </form>
        </div>
        <strong class="cart-line-total">{{ money(item.subtotal_cents) }}</strong>
      </article>
    </div>

    <aside class="panel summary-panel">
      <p class="eyebrow"><span class="signal-dot"></span> RESUMO</p>
      <h2>Seu pedido</h2>
      <dl class="totals">
        <div><dt>Subtotal</dt><dd>{{ money(cart.totalCents) }}</dd></div>
        <div><dt>Frete fixo</dt><dd>{{ money(cart.shippingCents) }}</dd></div>
        <div class="grand-total"><dt>Total</dt><dd>{{ money(cart.checkoutTotalCents) }}</dd></div>
      </dl>

      <p v-if="cart.hasUnavailable" class="muted mb-3">Ajuste os itens indisponíveis para continuar.</p>
      <p v-else-if="auth.ready && !auth.user" class="muted mb-3">Entre na sua conta para finalizar. O carrinho acompanha você.</p>

      <AppButton
        :to="{ name: 'checkout' }"
        color="primary"
        append-icon="mdi-arrow-right"
        block
        :disabled="locked || cart.hasUnavailable"
      >Ir para o checkout</AppButton>
      <AppButton :to="{ name: 'catalog' }" variant="text" block class="mt-2">Continuar comprando</AppButton>
    </aside>
  </div>
</template>
