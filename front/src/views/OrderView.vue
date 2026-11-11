<script setup>
import { computed, onMounted, ref } from 'vue'
import StatusChip from '../components/StatusChip.vue'
import api, { failure, mutate } from '../api/api'
import { addressLabel, date, money } from '../util/format'
import { useAuthStore } from '../stores/auth'

const props = defineProps({ id: { type: String, required: true } })
const auth = useAuthStore()
const order = ref(null)
const loading = ref(true)
const pending = ref('')
const error = ref('')
const confirmCancel = ref(false)
const isOwner = computed(() => String(order.value?.user_id) === String(auth.user?.id))
const canPay = computed(() => order.value?.status === 'pending_payment' && isOwner.value)
const canCancel = computed(() => order.value?.status === 'pending_payment' && (isOwner.value || auth.user?.role === 'admin'))

onMounted(async () => {
  try {
    const { data } = await api.get(`/orders/${props.id}`)
    order.value = data.data
  } catch (requestError) {
    error.value = requestError.response?.status === 404 ? 'Pedido não encontrado.' : failure(requestError)
  } finally {
    loading.value = false
  }
})

async function change(action) {
  if (pending.value) return

  pending.value = action
  error.value = ''
  try {
    const { data } = await mutate('post', `/orders/${props.id}/${action}`)
    order.value = data
    confirmCancel.value = false
  } catch (requestError) {
    error.value = failure(requestError)
    if (requestError.response?.status === 409) {
      try {
        order.value = (await api.get(`/orders/${props.id}`)).data.data
        confirmCancel.value = false
      } catch {
        // Mantém a mensagem da ação original.
      }
    }
  } finally {
    pending.value = ''
  }
}
</script>

<template>
  <AppButton :to="{ name: 'orders' }" variant="text" prepend-icon="mdi-arrow-left">Meus pedidos</AppButton>
  <AppProgress v-if="loading" class="mt-5" />
  <AppAlert v-if="error" type="error" class="mt-4">{{ error }}</AppAlert>

  <template v-if="order">
    <div class="page-heading section-heading">
      <div>
        <p class="eyebrow"><span class="signal-dot"></span> {{ date(order.created_at) }}</p>
        <h1 class="page-title">Pedido #{{ order.id }}</h1>
      </div>
      <StatusChip :status="order.status" />
    </div>

    <div class="commerce-grid">
      <section class="panel content-panel">
        <h2>Itens do pedido</h2>

        <ul class="plain-list summary-lines">
          <li v-for="item in order.items" :key="item.product_id">
            <span><strong>{{ item.product_name }}</strong><small>{{ item.quantity }} × {{ money(item.unit_price_cents) }}</small></span>
            <strong>{{ money(item.subtotal_cents) }}</strong>
          </li>
        </ul>

        <h2 class="mt-6 mb-2">Entrega</h2>
        <p class="muted">{{ addressLabel(order.shipping_address) }}</p>
      </section>

      <aside class="panel summary-panel">
        <p class="eyebrow">PAGAMENTO</p>
        <h2>Resumo financeiro</h2>
        <dl class="totals">
          <div><dt>Subtotal</dt><dd>{{ money(order.subtotal_cents) }}</dd></div>
          <div><dt>Frete</dt><dd>{{ money(order.shipping_cents) }}</dd></div>
          <div class="grand-total"><dt>Total</dt><dd>{{ money(order.total_cents) }}</dd></div>
        </dl>

        <p v-if="order.paid_at" class="muted">Pagamento registrado em {{ date(order.paid_at) }}.</p>
        <template v-if="canPay">
          <p class="muted mb-3">Pagamento simulado para o projeto acadêmico.</p>
          <AppButton color="primary" block :loading="pending === 'pay'" :disabled="Boolean(pending)" @click="change('pay')">
            Confirmar pagamento
          </AppButton>
        </template>

        <AppButton v-if="canCancel" variant="text" color="error" block class="mt-3" :disabled="Boolean(pending)" @click="confirmCancel = true">
          Cancelar pedido
        </AppButton>
      </aside>
    </div>

    <AppDialog v-model="confirmCancel" max-width="440" title="Cancelar este pedido?">
        <div class="dialog-body">As unidades serão devolvidas ao estoque da loja.</div>
        <div class="actions dialog-actions">
          <AppButton :disabled="Boolean(pending)" @click="confirmCancel = false">Voltar</AppButton>
          <AppButton color="error" :loading="pending === 'cancel'" @click="change('cancel')">Confirmar cancelamento</AppButton>
        </div>
      
    </AppDialog>
  </template>
</template>
