<script setup>
import { computed, ref, watch } from 'vue'
import { illustration } from '../util/format'

const props = defineProps({
  src: { type: String, default: null },
  category: { type: String, default: 'consoles' },
  eager: Boolean,
})

const failed = ref(false)
watch(
  () => props.src,
  () => {
    failed.value = false
  },
)
const source = computed(() =>
  !failed.value && props.src ? props.src : illustration(props.category),
)
</script>

<template>
  <img
    :src="source"
    alt=""
    :loading="eager ? 'eager' : 'lazy'"
    decoding="async"
    width="650"
    height="400"
    @error="failed = true"
  />
</template>
