<script setup>
defineProps({ color: { type: String, default: 'secondary' }, closable: Boolean })
const emit = defineEmits(['close'])
</script>

<template>
  <span class="app-chip" :class="'tone-' + color">
    <slot />
    <button v-if="closable" type="button" aria-label="Remover filtro" @click="emit('close')">×</button>
  </span>
</template>
