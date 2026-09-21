import { createApp, markRaw } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import routes from 'virtual:generated-pages'
import { useAuthStore } from './stores/authStore'
import App from './App.vue'

import '@unocss/reset/tailwind.css'
import './styles/main.css'
import 'uno.css'

const pinia = createPinia()
const app = createApp(App)

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  if (to.meta.requireAuth) {
    if (useAuthStore().isAuthenticated)
      next()
    else
      next('/login')
  }
  else {
    next()
  }
})

app.use(pinia)
pinia.use(({ store }) => {
  store.$router = markRaw(router)
})

app.use(router)
app.mount('#app')
