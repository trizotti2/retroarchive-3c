<script setup>
import { onMounted, ref, useId, watch } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  maxWidth: { type: [String, Number], default: 500 },
  persistent: Boolean,
})
const open = defineModel({ type: Boolean, required: true })
const dialog = ref(null)
const titleId = useId()

function sync() {
  if (open.value && !dialog.value.open) dialog.value.showModal()
  else if (!open.value && dialog.value.open) dialog.value.close()
}

function dismiss() {
  if (!props.persistent) open.value = false
}

onMounted(sync)
watch(open, sync, { flush: 'post' })
</script>

<template>
  <Teleport to="body">
    <dialog
      ref="dialog"
      class="app-dialog"
      :style="{ maxWidth: Number(maxWidth) + 'px' }"
      :aria-labelledby="titleId"
      @cancel.prevent="dismiss"
      @click.self="dismiss"
    >
      <section v-if="open" class="dialog-inner">
        <h2 :id="titleId">{{ title }}</h2>
        <slot />
      </section>
    </dialog>
  </Teleport>
</template>
