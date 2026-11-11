<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
  to: [String, Object],
  color: String,
  variant: String,
  size: String,
  prependIcon: String,
  appendIcon: String,
  type: { type: String, default: 'button' },
  block: Boolean,
  disabled: Boolean,
  loading: Boolean,
})

const disabled = computed(() => props.disabled || props.loading)
const link = computed(() => Boolean(props.to) && !disabled.value)
</script>

<template>
  <component
    :is="link ? RouterLink : 'button'"
    :to="link ? to : undefined"
    :type="link ? undefined : type"
    :disabled="link ? undefined : disabled"
    :aria-busy="loading || undefined"
    class="app-button"
    :class="[
      variant && 'button-' + variant,
      color && 'button-' + color,
      size && 'button-' + size,
      { 'button-block': block },
    ]"
  >
    <span v-if="loading" class="button-spinner" aria-hidden="true"></span>
    <span v-else-if="prependIcon" :class="['mdi', prependIcon]" aria-hidden="true"></span>
    <slot />
    <span v-if="appendIcon" :class="['mdi', appendIcon]" aria-hidden="true"></span>
  </component>
</template>
