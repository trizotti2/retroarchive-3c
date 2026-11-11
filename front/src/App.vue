<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { useCartStore } from './stores/cart'

const auth = useAuthStore()
const cart = useCartStore()
const route = useRoute()
const router = useRouter()
const accountMenu = ref(null)

watch(() => route.path, () => accountMenu.value?.removeAttribute('open'))

onMounted(() => auth.restoreSession())

watch(
  () => auth.ready ? (auth.user?.id ?? 'guest') : null,
  (owner) => {
    if (owner !== null) cart.load(true)
  },
  { immediate: true },
)

async function logout() {
  await auth.logout()
  if (!auth.user && route.meta.auth) await router.replace({ name: 'catalog' })
}
</script>

<template>
  <div class="store-app">
    <header class="site-header">
      <div class="shell site-header-inner">
        <RouterLink :to="{ name: 'catalog' }" class="brand" aria-label="Retro Archive, início">
          <img src="/media/mark.svg" alt="" width="34" height="34" />
          <span>RETRO<span class="accent">/</span>ARCHIVE<small>LOJA DE GAMES RETRÔ</small></span>
        </RouterLink>

        <nav class="main-nav" aria-label="Navegação principal">
          <RouterLink :to="{ name: 'catalog' }" exact-active-class="nav-active">Catálogo</RouterLink>
          <RouterLink v-if="auth.user" :to="{ name: 'orders' }" active-class="nav-active">Pedidos</RouterLink>
          <RouterLink v-if="auth.user?.role === 'admin'" :to="{ name: 'admin' }" active-class="nav-active">Painel</RouterLink>
        </nav>

        <div class="nav-actions">
          <AppButton :to="{ name: 'cart' }" variant="tonal" class="nav-button" prepend-icon="mdi-cart-outline" :aria-label="`Carrinho, ${cart.count} itens`">
            <span class="cart-label">Carrinho</span><span class="cart-count">{{ cart.count }}</span>
          </AppButton>
          <AppButton v-if="!auth.user" :to="{ name: 'login' }" color="primary" class="nav-button">Entrar</AppButton>
          <details v-else ref="accountMenu" class="account-menu">
            <summary class="app-button button-outlined nav-button">
              <span class="mdi mdi-account-outline" aria-hidden="true"></span>
              <span class="account-label">{{ auth.user.name }}</span>
              <span class="account-mobile">Conta</span>
            </summary>
            <nav aria-label="Minha conta">
              <RouterLink :to="{ name: 'profile' }">Meu perfil</RouterLink>
              <RouterLink :to="{ name: 'orders' }">Meus pedidos</RouterLink>
              <RouterLink v-if="auth.user.role === 'admin'" :to="{ name: 'admin' }">Administração</RouterLink>
              <button type="button" :disabled="cart.busy || auth.pending" @click="logout">Sair</button>
            </nav>
          </details>
        </div>
      </div>
    </header>

    <main>
      <div class="shell main-content">
        <AppAlert v-if="auth.error" type="error" class="mb-5">{{ auth.error }}</AppAlert>

        <AppAlert v-if="auth.cartAdjustments.length" type="warning" class="mb-5">
          <strong>Seu carrinho foi ajustado ao estoque atual.</strong>
          <ul class="ml-4 mt-1">
            <li v-for="item in auth.cartAdjustments" :key="item.product_id">
              {{ item.name }}: {{ item.accepted ? `de ${item.requested} para ${item.accepted} unidade(s)` : 'removido por indisponibilidade' }}.
            </li>
          </ul>
          <AppButton variant="text" size="small" class="mt-2" @click="auth.cartAdjustments = []">Entendi</AppButton>
        </AppAlert>

        <RouterView :key="route.path" />
      </div>
    </main>

    <footer class="site-footer">
      <div class="shell footer-inner">
        <span class="mono"><span class="accent">RA</span> // RETRO ARCHIVE</span>
        <span>Projeto acadêmico · pagamento simulado</span>
        <div class="actions"><RouterLink :to="{ name: 'docs' }">Documentação da API</RouterLink><a href="/creditos-imagens.html">Créditos das imagens</a></div>
      </div>
    </footer>
  </div>
</template>
