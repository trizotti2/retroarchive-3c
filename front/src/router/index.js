import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import CatalogView from '../views/CatalogView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/catalogo',
      name: 'catalogo',
      component: CatalogView
    },
    {
  path: '/entrar',
  name: 'login',
  component: AuthPage,
  props: { mode: 'login' },
},
{
  path: '/cadastro',
  name: 'register',
  component: AuthPage,
  props: { mode: 'register' },
},
  ],
})

export default router
