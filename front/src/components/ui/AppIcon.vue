<script setup>
defineProps({
  icon: { type: String, required: true },
  size: { type: [String, Number], default: 20 },
  color: String,
})
</script>

<template>
  <span :class="['mdi', icon, { accent: color === 'primary' }]" :style="{ fontSize: size + 'px' }" aria-hidden="true"></span>
</template>
