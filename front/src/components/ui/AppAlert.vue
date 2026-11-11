<script setup>
defineProps({ type: { type: String, default: 'warning' } })
</script>

<template>
  <div class="app-alert" :class="'tone-' + type" :role="type === 'error' ? 'alert' : 'status'">
    <slot />
  </div>
</template>
