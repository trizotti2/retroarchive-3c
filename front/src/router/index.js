import { createRouter, createWebHashHistory } from 'vue-router'

export default createRouter({
  history: createWebHashHistory(),
  scrollBehavior(to, from, saved) {
    if (saved) return saved
    if (to.path !== from.path) return { top: 0 }
  },
  routes: [
    {
      path: '/',
      alias: '/catalogo',
      name: 'catalog',
      component: () => import('../views/CatalogView.vue'),
    },
    {
      path: '/produtos/:id(\\d+)',
      name: 'product',
      props: true,
      component: () => import('../views/ProductView.vue'),
    },
    { path: '/autenticacao', redirect: { name: 'login' } },
    { path: '/carrinho', name: 'cart', component: () => import('../views/CartView.vue') },
    {
      path: '/entrar',
      name: 'login',
      props: { mode: 'login' },
      component: () => import('../views/AuthView.vue'),
    },
    {
      path: '/cadastro',
      name: 'register',
      props: { mode: 'register' },
      component: () => import('../views/AuthView.vue'),
    },
    {
      path: '/checkout',
      name: 'checkout',
      meta: { auth: true },
      component: () => import('../views/CheckoutView.vue'),
    },
    {
      path: '/pedidos',
      name: 'orders',
      meta: { auth: true },
      component: () => import('../views/OrdersView.vue'),
    },
    {
      path: '/pedidos/:id(\\d+)',
      name: 'order',
      props: true,
      meta: { auth: true },
      component: () => import('../views/OrderView.vue'),
    },
    {
      path: '/perfil',
      name: 'profile',
      meta: { auth: true },
      component: () => import('../views/ProfileView.vue'),
    },
    {
      path: '/admin',
      name: 'admin',
      meta: { auth: true, admin: true },
      component: () => import('../views/AdminView.vue'),
    },
    { path: '/docs', name: 'docs', component: () => import('../views/DocsView.vue') },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../views/NotFoundView.vue'),
    },
  ],
})
