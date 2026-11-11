<script setup>
import { computed, useAttrs, useId } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  label: { type: String, required: true },
  type: { type: String, default: 'text' },
  hint: String,
  hideDetails: [Boolean, String],
  errorMessages: [String, Array],
  prependInnerIcon: String,
})
const model = defineModel()
const id = useId()
const attrs = useAttrs()
const inputAttrs = () => Object.fromEntries(
  Object.entries(attrs).filter(([key]) => key !== 'class' && key !== 'style'),
)
const errors = computed(() => Array.isArray(props.errorMessages)
  ? props.errorMessages.filter(Boolean)
  : [props.errorMessages].filter(Boolean),
)
const details = computed(() => errors.value.length > 0 || (!props.hideDetails || props.hideDetails === 'auto') && props.hint)

function update(event) {
  model.value = props.type === 'file'
    ? (event.target.files?.[0] ?? null)
    : event.target.value
}
</script>

<template>
  <div class="app-field" :class="$attrs.class" :style="$attrs.style">
    <label class="field-label" :for="id">{{ label }}</label>
    <span class="field-control" :class="{ 'has-icon': prependInnerIcon }">
      <span v-if="prependInnerIcon" :class="['mdi', prependInnerIcon, 'field-icon']" aria-hidden="true"></span>
      <component
        :is="type === 'textarea' ? 'textarea' : 'input'"
        v-bind="inputAttrs()"
        :id="id"
        :type="type === 'textarea' ? undefined : type"
        :value="type === 'file' ? undefined : (model ?? '')"
        :aria-invalid="errors.length > 0 || undefined"
        :aria-describedby="details ? id + '-details' : undefined"
        @input="update"
      />
    </span>
    <span v-if="details" :id="id + '-details'" class="field-details">
      <span v-if="errors.length" class="field-error" role="alert">{{ errors.join(' ') }}</span>
      <span v-else>{{ hint }}</span>
    </span>
  </div>
</template>
