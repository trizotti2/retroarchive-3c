<script setup>
import { computed } from 'vue'

const props = defineProps({
  length: { type: Number, required: true },
  disabled: Boolean,
})
const page = defineModel({ type: Number, required: true })

const pages = computed(() => {
  const first = Math.max(1, Math.min(page.value - 2, props.length - 4))
  const last = Math.min(props.length, first + 4)
  return Array.from({ length: last - first + 1 }, (_, index) => first + index)
})
</script>

<template>
  <nav class="app-pagination" aria-label="Paginação">
    <button type="button" aria-label="Página anterior" :disabled="disabled || page <= 1" @click="page--">‹</button>
    <button
      v-for="number in pages"
      :key="number"
      type="button"
      :aria-current="page === number ? 'page' : undefined"
      :aria-label="'Página ' + number"
      :disabled="disabled"
      @click="page = number"
    >{{ number }}</button>
    <button type="button" aria-label="Próxima página" :disabled="disabled || page >= length" @click="page++">›</button>
  </nav>
</template>
