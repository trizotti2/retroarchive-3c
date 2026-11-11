import '@mdi/font/css/materialdesignicons.css'
import '@fontsource/space-grotesk/latin-400.css'
import '@fontsource/space-grotesk/latin-500.css'
import '@fontsource/space-grotesk/latin-600.css'
import '@fontsource/space-grotesk/latin-700.css'
import '@fontsource/ibm-plex-mono/latin-500.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import AppButton from './components/ui/AppButton.vue'
import AppField from './components/ui/AppField.vue'
import AppSelect from './components/ui/AppSelect.vue'
import AppDialog from './components/ui/AppDialog.vue'
import AppPagination from './components/ui/AppPagination.vue'
import AppAlert from './components/ui/AppAlert.vue'
import AppChip from './components/ui/AppChip.vue'
import AppProgress from './components/ui/AppProgress.vue'
import AppIcon from './components/ui/AppIcon.vue'
import api from './api/api'
import router from './router'
import { useAuthStore } from './stores/auth'
import './assets/main.css'

const pinia = createPinia()
const auth = useAuthStore(pinia)

router.beforeEach(async (to) => {
  if (!to.meta.auth) return true

  await auth.restoreSession()
  if (!auth.user) return { name: 'login', query: { next: to.fullPath } }
  if (to.meta.admin && auth.user.role !== 'admin') return { name: 'catalog' }

  return true
})

api.interceptors.response.use((response) => response, (error) => {
  if (error.response?.status === 401 && auth.user) {
    auth.clearUser()
    const current = router.currentRoute.value
    if (current.meta.auth) router.replace({ name: 'login', query: { next: current.fullPath } })
  }

  return Promise.reject(error)
})

const app = createApp(App).use(pinia).use(router)
const components = { AppButton, AppField, AppSelect, AppDialog, AppPagination, AppAlert, AppChip, AppProgress, AppIcon }
for (const [name, component] of Object.entries(components)) {
  app.component(name, component)
}
app.mount('#app')
