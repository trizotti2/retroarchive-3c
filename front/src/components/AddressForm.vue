<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  address: { type: Object, default: null },
  errors: { type: Object, default: () => ({}) },
  busy: Boolean,
})

const emit = defineEmits(['save', 'cancel'])
const fields = [
  { key: 'recipient', label: 'Destinatário', full: true, maxlength: 120 },
  { key: 'postal_code', label: 'CEP', maxlength: 9, inputmode: 'numeric' },
  { key: 'number', label: 'Número', maxlength: 20 },
  { key: 'street', label: 'Rua ou avenida', full: true, maxlength: 160 },
  { key: 'neighborhood', label: 'Bairro', maxlength: 100 },
  { key: 'complement', label: 'Complemento (opcional)', maxlength: 100 },
  { key: 'city', label: 'Cidade', maxlength: 100 },
  { key: 'state', label: 'UF', maxlength: 2 },
]
const form = reactive({})

watch(() => props.address, (address) => {
  for (const field of fields) form[field.key] = address?.[field.key] ?? ''
}, { immediate: true })

function save() {
  if (props.busy) return

  const data = Object.fromEntries(fields.map(({ key }) => [key, form[key].trim()]))
  data.state = data.state.toUpperCase()
  emit('save', data)
}
</script>

<template>
  <form @submit.prevent="save"><fieldset :disabled="busy">
    <div class="form-grid">
      <AppField
        v-for="field in fields"
        :key="field.key"
        v-model="form[field.key]"
        :class="{ 'span-all': field.full }"
        :label="field.label"
        :maxlength="field.maxlength"
        :inputmode="field.inputmode"
        :error-messages="errors[field.key]"
        hide-details="auto"
      />
    </div>

    <div class="actions mt-4">
      <AppButton type="submit" color="primary" :loading="busy">Salvar endereço</AppButton>
      <AppButton type="button" variant="text" :disabled="busy" @click="emit('cancel')">Cancelar</AppButton>
    </div>
  </fieldset></form>
</template>
