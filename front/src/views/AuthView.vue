<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const props = defineProps({
  mode: { type: String, required: true },
})

const router = useRouter()
const auth = useAuthStore()
const isRegister = computed(() => props.mode === 'register')

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const message = ref('')

async function submit() {
  if (!auth.ready || auth.pending || auth.user) return

  errors.value = {}
  message.value = ''

  const fields = {
    email: form.email.trim(),
    password: form.password,
  }

  if (isRegister.value) {
    fields.name = form.name.trim()
    fields.password_confirmation = form.password_confirmation
  }

  try {
    await auth.authenticate(props.mode, fields)
  } catch (error) {
    const status = error.response?.status

    if (status === 422) {
      errors.value = error.response.data.errors ?? {}
      message.value = 'Confira os campos destacados.'
    } else if (status === 419) {
      message.value = 'A sessão expirou. Envie o formulário novamente.'
    } else if (status === 429) {
      message.value = 'Muitas tentativas. Aguarde um momento e tente de novo.'
    } else {
      message.value = 'Não foi possível concluir. Tente novamente.'
    }

    return
  }

  await router.push({ name: 'catalog' })
}
</script>

<template>
  <v-card class="mx-auto" max-width="480">
    <v-card-title>
      {{ isRegister ? 'Criar conta' : 'Entrar' }}
    </v-card-title>

    <v-card-text>
      <template v-if="auth.user">
        <p class="mb-4">Você está conectado como {{ auth.user.name }}.</p>
        <v-btn :to="{ name: 'catalog' }">Voltar ao catálogo</v-btn>
      </template>

      <v-form
        v-else
        :disabled="!auth.ready || auth.pending"
        @submit.prevent="submit"
      >
        <v-alert v-if="message" type="error" class="mb-4">
          {{ message }}
        </v-alert>

        <v-text-field
          v-if="isRegister"
          v-model="form.name"
          label="Nome"
          autocomplete="name"
          :error-messages="errors.name ?? []"
        />

        <v-text-field
          v-model="form.email"
          label="E-mail"
          type="email"
          autocomplete="email"
          :error-messages="errors.email ?? []"
        />

        <v-text-field
          v-model="form.password"
          label="Senha"
          type="password"
          :autocomplete="isRegister ? 'new-password' : 'current-password'"
          :error-messages="errors.password ?? []"
        />

        <v-text-field
          v-if="isRegister"
          v-model="form.password_confirmation"
          label="Confirme a senha"
          type="password"
          autocomplete="new-password"
          :error-messages="errors.password_confirmation ?? []"
        />

        <v-btn
          type="submit"
          color="primary"
          :disabled="!auth.ready || auth.pending"
          :loading="auth.pending"
        >
          {{ isRegister ? 'Criar conta' : 'Entrar' }}
        </v-btn>

        <v-btn
          :to="{ name: isRegister ? 'login' : 'register' }"
          :disabled="auth.pending"
          variant="text"
        >
          {{ isRegister ? 'Já tenho conta' : 'Criar uma conta' }}
        </v-btn>
      </v-form>
    </v-card-text>
  </v-card>
</template>