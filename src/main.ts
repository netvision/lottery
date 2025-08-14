import { createApp, markRaw } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import routes from 'virtual:generated-pages'
import { initializeApp } from 'firebase/app'
import { getAuth, onAuthStateChanged } from 'firebase/auth'
import { useAuthStore } from './stores/authStore'
import App from './App.vue'

import '@unocss/reset/tailwind.css'
import './styles/main.css'
import 'uno.css'

const firebaseConfig = {
  apiKey: import.meta.env.VITE_APIKEY,
  authDomain: import.meta.env.VITE_AUTHDOMAIN,
  projectId: import.meta.env.VITE_PROJECTID,
  storageBucket: import.meta.env.VITE_STORAGEBUCKET,
  messagingSenderId: import.meta.env.VITE_MESSAGINGSENDERID,
  appId: import.meta.env.VITE_APPID,
}

initializeApp(firebaseConfig)

// eslint-disable-next-line antfu/top-level-function
const getCurrentUser = () => {
  return new Promise((resolve, reject) => {
    const removeListener = onAuthStateChanged(
      getAuth(),
      (user) => {
        removeListener()
        resolve(user)
      },
      reject,
    )
  })
}

const pinia = createPinia()
const app = createApp(App)

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  if (to.meta.requireAuth) {
    if (await getCurrentUser())
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

// Initialize auth state listener after Pinia is set up
const auth = getAuth()
const authStore = useAuthStore()

onAuthStateChanged(auth, (user) => {
  authStore.setUser(user)
})

app.use(router)
app.mount('#app')
