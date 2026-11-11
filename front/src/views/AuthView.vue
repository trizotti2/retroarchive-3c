<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { failure } from '../api/api'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'

const props = defineProps({ mode: { type: String, required: true } })
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const cart = useCartStore()
const register = computed(() => props.mode === 'register')
const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const errors = ref({})
const error = ref('')

function nextDestination() {
  if (auth.user?.role === 'admin') return { name: 'admin' }

  const next = route.query.next
  if (typeof next === 'string' && next.startsWith('/') && !next.startsWith('//')) {
    const target = router.resolve(next)
    if (target.meta.auth && !target.meta.admin) return target.fullPath
  }

  return { name: 'catalog' }
}

async function submit() {
  if (auth.pending || auth.user || !auth.ready || cart.busy) return

  errors.value = {}
  error.value = ''
  const fields = { email: form.email.trim(), password: form.password }

  if (register.value) {
    fields.name = form.name.trim()
    fields.password_confirmation = form.password_confirmation
  }

  try {
    await auth.authenticate(props.mode, fields)
  } catch (requestError) {
    errors.value = requestError.response?.data?.errors ?? {}
    error.value = failure(requestError, 'Não foi possível acessar a conta.')
    return
  }

  await router.replace(nextDestination())
}
</script>

<template>
  <div class="auth-layout">
    <div class="auth-aside">
      <img src="/media/consoles.svg" alt="" width="640" height="400" />
      <div>
        <p class="eyebrow"><span class="signal-dot"></span> RETRO ARCHIVE</p>
        <h2>Continue de onde parou.</h2>
        <p>Seu carrinho acompanha você ao entrar.</p>
      </div>
    </div>
    <section class="panel auth-panel">
      <p class="eyebrow"><span class="signal-dot"></span> SUA CONTA</p>
      <h1 class="page-title">{{ register ? 'Criar conta' : 'Entrar' }}</h1>

      <template v-if="auth.user">
        <p class="muted mb-4">Você está conectado como {{ auth.user.name }}.</p>
        <AppButton :to="nextDestination()" color="primary">Continuar</AppButton>
      </template>

      <form v-else @submit.prevent="submit"><fieldset :disabled="auth.pending">
        <AppAlert v-if="error" type="error" class="mb-4">{{ error }}</AppAlert>
        <AppField v-if="register" v-model="form.name" label="Nome" autocomplete="name" :error-messages="errors.name" />
        <AppField v-model="form.email" label="E-mail" type="email" autocomplete="email" :error-messages="errors.email" />
        <AppField
          v-model="form.password"
          label="Senha"
          type="password"
          :autocomplete="register ? 'new-password' : 'current-password'"
          :error-messages="errors.password"
        />
        <AppField
          v-if="register"
          v-model="form.password_confirmation"
          label="Confirme a senha"
          type="password"
          autocomplete="new-password"
          :error-messages="errors.password_confirmation"
        />
        <div class="actions">
          <AppButton type="submit" color="primary" :loading="auth.pending" :disabled="!auth.ready || cart.busy">
            {{ register ? 'Criar conta' : 'Entrar' }}
          </AppButton>
          <AppButton
            :to="{ name: register ? 'login' : 'register', query: route.query }"
            variant="text"
            :disabled="auth.pending"
          >{{ register ? 'Já tenho conta' : 'Criar uma conta' }}</AppButton>
        </div>
      </fieldset></form>
    </section>
  </div>
</template>
