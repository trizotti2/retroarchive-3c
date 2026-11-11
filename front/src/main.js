import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHashHistory } from 'vue-router'
import { createVuetify } from 'vuetify'
import App from './App.vue'
import CatalogView from './views/CatalogView.vue'
import ProductView from './views/ProductView.vue'
import AuthView from './views/AuthView.vue'
import CartView from './views/CartView.vue'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    { path: '/', name: 'catalogo', component: CatalogView },
    {
      path: '/produtos/:id',
      name: 'product',
      component: ProductView,
      props: true,
    },
    {
      path: '/autenticacao',
      name: 'autenticacao',
      component: AuthView,
      props: true,
    },
    { path: '/carrinho', name: 'cart', component: CartPage },
  ],
})

createApp(App).use(createPinia()).use(router).use(createVuetify()).mount('#app')
