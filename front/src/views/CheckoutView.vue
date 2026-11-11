<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AddressForm from '../components/AddressForm.vue'
import api, { failure, mutate } from '../api/api'
import { addressLabel, money } from '../util/format'
import { useCartStore } from '../stores/cart'

const router = useRouter()
const cart = useCartStore()
const addresses = ref([])
const addressId = ref(null)
const showAddressForm = ref(false)
const loading = ref(true)
const savingAddress = ref(false)
const placingOrder = ref(false)
const addressErrors = ref({})
const addressError = ref('')
const orderError = ref('')
const busy = computed(() => loading.value || savingAddress.value || placingOrder.value || cart.busy)
const addressOptions = computed(() => addresses.value.map((address) => ({ title: addressLabel(address), value: address.id })))

async function loadAddresses() {
  loading.value = true
  addressError.value = ''
  try {
    const { data } = await api.get('/addresses')
    addresses.value = data.data
    addressId.value = addresses.value[0]?.id ?? null
    showAddressForm.value = addresses.value.length === 0
  } catch (error) {
    addressError.value = failure(error, 'Não foi possível carregar os endereços.')
  } finally {
    loading.value = false
  }
}

async function saveAddress(fields) {
  savingAddress.value = true
  addressError.value = ''
  addressErrors.value = {}
  try {
    const { data: address } = await mutate('post', '/addresses', fields)
    addresses.value.unshift(address)
    addressId.value = address.id
    showAddressForm.value = false
  } catch (error) {
    addressErrors.value = error.response?.data?.errors ?? {}
    addressError.value = failure(error, 'Não foi possível salvar o endereço.')
  } finally {
    savingAddress.value = false
  }
}

async function placeOrder() {
  if (busy.value || !addressId.value || cart.hasUnavailable || !cart.items.length) return

  placingOrder.value = true
  orderError.value = ''
  try {
    const { data: order } = await mutate('post', '/orders', { address_id: addressId.value })
    await cart.load(true)
    await router.replace({ name: 'order', params: { id: order.id } })
  } catch (error) {
    orderError.value = failure(error, 'Não foi possível confirmar o pedido.')
    if (error.response?.status === 422) await cart.load()
  } finally {
    placingOrder.value = false
  }
}

onMounted(loadAddresses)
</script>

<template>
  <AppButton :to="{ name: 'cart' }" variant="text" prepend-icon="mdi-arrow-left">Carrinho</AppButton>

  <div class="page-heading">
    <p class="eyebrow"><span class="signal-dot"></span> ENTREGA E REVISÃO</p>
    <h1 class="page-title">Finalizar compra</h1>
  </div>

  <AppProgress v-if="loading || cart.busy" class="mb-4" />
  <AppAlert v-if="orderError || cart.error" type="error" class="mb-4">{{ orderError || cart.error }}</AppAlert>
  <AppAlert v-if="cart.hasUnavailable" type="warning" class="mb-4">
    Há itens indisponíveis. Volte ao carrinho para ajustar as quantidades.
  </AppAlert>

  <div v-if="!cart.busy && !cart.items.length" class="empty-state panel">
    <h2>Seu carrinho está vazio</h2>
    <AppButton :to="{ name: 'catalog' }" color="primary">Ver catálogo</AppButton>
  </div>

  <div v-else class="commerce-grid">
    <section class="panel content-panel">
      <p class="eyebrow">01 / ENTREGA</p>
      <h2 class="mb-4">Endereço de entrega</h2>
      <AppAlert v-if="addressError" type="error" class="mb-4">
        {{ addressError }}
        <AppButton v-if="!showAddressForm" variant="text" size="small" @click="loadAddresses">Tentar novamente</AppButton>
      </AppAlert>

      <AppSelect v-if="addresses.length" v-model="addressId" label="Escolher endereço salvo" :items="addressOptions" :disabled="busy" />

      <AddressForm
        v-if="showAddressForm"
        :errors="addressErrors"
        :busy="savingAddress || placingOrder"
        @save="saveAddress"
        @cancel="showAddressForm = false"
      />
      <AppButton v-else variant="tonal" prepend-icon="mdi-map-marker-plus-outline" :disabled="busy" @click="showAddressForm = true">
        Novo endereço
      </AppButton>
    </section>

    <aside class="panel summary-panel">
      <p class="eyebrow">02 / REVISÃO</p>
      <h2>Resumo do pedido</h2>

      <ul class="plain-list summary-lines">
        <li v-for="item in cart.items" :key="item.id">
          <span><strong>{{ item.name }}</strong><small>{{ item.quantity }} × {{ money(item.unit_price_cents) }}</small></span>
          <strong>{{ money(item.subtotal_cents) }}</strong>
        </li>
      </ul>

      <dl class="totals">
        <div><dt>Subtotal</dt><dd>{{ money(cart.totalCents) }}</dd></div>
        <div><dt>Frete fixo</dt><dd>{{ money(cart.shippingCents) }}</dd></div>
        <div class="grand-total"><dt>Total</dt><dd>{{ money(cart.checkoutTotalCents) }}</dd></div>
      </dl>

      <AppButton
        color="primary"
        block
        :loading="placingOrder"
        :disabled="busy || !addressId || cart.hasUnavailable || !cart.items.length"
        @click="placeOrder"
      >Confirmar pedido</AppButton>
    </aside>
  </div>
</template>
