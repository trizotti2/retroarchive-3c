<script setup>
import { onMounted, watch } from 'vue'
import { useAuthStore } from './stores/auth'
import { useCartStore } from './stores/cart'

const auth = useAuthStore()
const cart = useCartStore()

onMounted(() => auth.restoreSession())

watch(
  () => auth.ready ? (auth.user?.id ?? 'guest') : null,
  (owner) => {
    if (owner !== null) cart.load()
  },
  { immediate: true },
)
</script>

<template>
  <v-app>
    <v-app-bar color="indigo-darken-4">
      <v-app-bar-title>
        <RouterLink
          :to="{ name: 'catalog' }"
          class="text-white text-decoration-none"
        >
          Loja Retro
        </RouterLink>
      </v-app-bar-title>

      <v-btn :to="{ name: 'cart' }" size="small">
        Carrinho ({{ cart.count }})
      </v-btn>

      <template v-if="auth.user">
        <span class="d-none d-md-inline mx-2">{{ auth.user.name }}</span>

        <v-btn
          size="small"
          :disabled="cart.busy"
          :loading="auth.pending"
          @click="auth.logout()"
        >
          Sair
        </v-btn>
      </template>

      <template v-else>
        <v-btn
          :to="{ name: 'login' }"
          :disabled="auth.pending"
          size="small"
        >
          Entrar
        </v-btn>

        <v-btn
          :to="{ name: 'register' }"
          :disabled="auth.pending"
          size="small"
          class="d-none d-sm-inline-flex"
        >
          Criar conta
        </v-btn>
      </template>
    </v-app-bar>

    <v-main>
      <v-container class="py-6">
        <v-progress-linear v-if="!auth.ready" indeterminate class="mb-4" />

        <v-alert v-if="auth.error" type="error" class="mb-4">
          {{ auth.error }}
        </v-alert>

        <v-alert
          v-if="auth.cartAdjustments?.length"
          type="warning"
          class="mb-4"
        >
          <p class="font-weight-bold mb-2">
            O carrinho foi ajustado ao estoque atual.
          </p>

          <ul class="ml-5">
            <li v-for="item in auth.cartAdjustments" :key="item.product_id">
              <template v-if="item.accepted === 0">
                {{ item.name }} saiu do carrinho por indisponibilidade.
              </template>
              <template v-else>
                {{ item.name }} passou de {{ item.requested }} para
                {{ item.accepted }} unidade(s).
              </template>
            </li>
          </ul>

          <v-btn variant="text" class="mt-2" @click="auth.cartAdjustments = []">
            Entendi
          </v-btn>
        </v-alert>

        <RouterView :key="$route.path" />
      </v-container>
    </v-main>
  </v-app>
</template>