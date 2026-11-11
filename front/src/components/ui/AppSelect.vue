<script setup>
import { computed, useId } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  items: { type: Array, default: () => [] },
  itemTitle: { type: String, default: 'title' },
  itemValue: { type: String, default: 'value' },
  disabled: Boolean,
  errorMessages: [String, Array],
})
const model = defineModel()
const id = useId()
const options = computed(() => props.items.map((item) => {
  return typeof item === 'object'
    ? { label: item[props.itemTitle], value: item[props.itemValue] }
    : { label: item, value: item }
}))
const selected = computed(() => options.value.findIndex((item) => item.value === model.value))
const message = computed(() => Array.isArray(props.errorMessages)
  ? props.errorMessages.filter(Boolean).join(' ')
  : props.errorMessages,
)

function change(event) {
  model.value = options.value[Number(event.target.value)]?.value ?? null
}
</script>

<template>
  <div class="app-field">
    <label class="field-label" :for="id">{{ label }}</label>
    <select
      :id="id"
      :value="selected"
      :disabled="disabled"
      :aria-invalid="Boolean(message) || undefined"
      :aria-describedby="message ? id + '-details' : undefined"
      @change="change"
    >
      <option :value="-1" disabled>Selecione</option>
      <option v-for="(item, index) in options" :key="index" :value="index">{{ item.label }}</option>
    </select>
    <span v-if="message" :id="id + '-details'" class="field-details field-error" role="alert">{{ message }}</span>
  </div>
</template>
