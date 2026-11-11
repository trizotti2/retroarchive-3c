<script setup>
import { onMounted, reactive, ref } from 'vue'
import AddressForm from '../components/AddressForm.vue'
import api, { failure, mutate } from '../api/api'
import { addressLabel } from '../util/format'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const profile = reactive({ name: auth.user?.name ?? '', email: auth.user?.email ?? '' })
const profileErrors = ref({})
const profileError = ref('')
const saved = ref(false)
const savingProfile = ref(false)

const addresses = ref([])
const loading = ref(true)
const addressErrors = ref({})
const addressError = ref('')
const addressBusy = ref(false)
const editing = ref(false)
const selected = ref(null)
const deleting = ref(null)

async function saveProfile() {
  if (savingProfile.value) return
  savingProfile.value = true
  profileErrors.value = {}
  profileError.value = ''
  saved.value = false
  try {
    const { data } = await mutate('patch', '/profile', profile)
    auth.user = data
    profile.name = data.name
    profile.email = data.email
    saved.value = true
  } catch (error) {
    profileErrors.value = error.response?.data?.errors ?? {}
    profileError.value = failure(error)
  } finally {
    savingProfile.value = false
  }
}

async function loadAddresses() {
  loading.value = true
  addressError.value = ''
  try {
    const { data } = await api.get('/addresses')
    addresses.value = data.data
  } catch (error) {
    addressError.value = failure(error, 'Não foi possível carregar os endereços.')
  } finally {
    loading.value = false
  }
}

function edit(address = null) {
  addressErrors.value = {}
  addressError.value = ''
  selected.value = address
  editing.value = true
}

async function saveAddress(fields) {
  addressBusy.value = true
  addressErrors.value = {}
  addressError.value = ''
  try {
    const { data } = selected.value
      ? await mutate('patch', `/addresses/${selected.value.id}`, fields)
      : await mutate('post', '/addresses', fields)
    addresses.value = [data, ...addresses.value.filter((item) => item.id !== data.id)]
    editing.value = false
  } catch (error) {
    addressErrors.value = error.response?.data?.errors ?? {}
    addressError.value = failure(error)
  } finally {
    addressBusy.value = false
  }
}

async function removeAddress() {
  if (!deleting.value || addressBusy.value) return
  const id = deleting.value.id
  addressBusy.value = true
  addressError.value = ''
  try {
    await mutate('delete', `/addresses/${id}`)
    addresses.value = addresses.value.filter((item) => item.id !== id)
  } catch (error) {
    addressError.value = failure(error)
  } finally {
    deleting.value = null
    addressBusy.value = false
  }
}

onMounted(loadAddresses)
</script>

<template>
  <div class="page-heading">
    <p class="eyebrow"><span class="signal-dot"></span> SUA CONTA</p>
    <h1 class="page-title">Meu perfil</h1>
  </div>

  <div class="profile-grid">
    <section class="panel content-panel">
      <h2 class="mb-4">Dados da conta</h2>
      <AppAlert v-if="saved" type="success" class="mb-4">Dados atualizados.</AppAlert>
      <AppAlert v-if="profileError" type="error" class="mb-4">{{ profileError }}</AppAlert>

      <form @submit.prevent="saveProfile"><fieldset :disabled="savingProfile">
        <AppField v-model.trim="profile.name" label="Nome" autocomplete="name" :error-messages="profileErrors.name" />
        <AppField v-model.trim="profile.email" label="E-mail" type="email" autocomplete="email" :error-messages="profileErrors.email" />
        <AppButton type="submit" color="primary" :loading="savingProfile">Salvar dados</AppButton>
      </fieldset></form>
    </section>

    <section class="panel content-panel">
      <div class="section-heading mb-4">
        <h2>Endereços</h2>
        <AppButton v-if="!editing" variant="tonal" :disabled="loading || addressBusy" @click="edit()">Novo endereço</AppButton>
      </div>

      <AppProgress v-if="loading" />
      <AppAlert v-if="addressError" type="error" class="mb-4">{{ addressError }}</AppAlert>

      <AddressForm
        v-if="editing"
        :key="selected?.id ?? 'new'"
        :address="selected"
        :errors="addressErrors"
        :busy="addressBusy"
        @save="saveAddress"
        @cancel="editing = false"
      />
      <template v-else-if="!loading">
        <p v-if="!addresses.length && !addressError" class="muted">Nenhum endereço cadastrado.</p>
        <div v-for="address in addresses" :key="address.id" class="address-row">
          <p>{{ addressLabel(address) }}</p>
          <div class="actions">
            <AppButton size="small" variant="tonal" :disabled="addressBusy" @click="edit(address)">Editar</AppButton>
            <AppButton size="small" variant="text" color="error" :disabled="addressBusy" @click="deleting = address">Excluir</AppButton>
          </div>
        </div>
      </template>
    </section>
  </div>

  <AppDialog :model-value="Boolean(deleting)" max-width="440" @update:model-value="!$event && (deleting = null)" title="Excluir este endereço?">
      <div class="dialog-body">{{ addressLabel(deleting) }}</div>
      <div class="actions dialog-actions">
        <AppButton :disabled="addressBusy" @click="deleting = null">Voltar</AppButton>
        <AppButton color="error" :loading="addressBusy" @click="removeAddress">Excluir endereço</AppButton>
      </div>

  </AppDialog>
</template>
