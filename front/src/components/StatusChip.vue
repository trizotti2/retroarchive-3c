<script setup>
import { computed } from 'vue'
import { orderStatuses } from '../util/format'

const props = defineProps({ status: { type: String, required: true } })
const colors = {
  pending_payment: 'warning', paid: 'success', shipped: 'secondary',
  delivered: 'success', cancelled: 'error',
}
const color = computed(() => colors[props.status] ?? 'secondary')
</script>

<template>
  <AppChip :color="color">{{ orderStatuses[status] ?? status }}</AppChip>
</template>
