<!-- eslint-disable vue/html-self-closing -->
<script setup>
import { getAuth, onAuthStateChanged } from 'firebase/auth'
import { useAuthStore } from './stores/authStore'
import { isDark, toggleDark } from '~/composables'
import logo from '~/assets/ani-logo.gif'
import blaast from '~/assets/blaast.gif'

const authStore = useAuthStore()
const auth = getAuth()
onAuthStateChanged(auth, (user) => {
  if (user) {
    authStore.isLoggedIn = true
    authStore.name = user.displayName
    authStore.uid = user.uid
    authStore.email = user.email
    authStore.photoURL = user.photoURL
  }
  else {
    authStore.isLoggedIn = false
    authStore.name = ''
    authStore.uid = ''
    authStore.email = ''
    authStore.photoURL = ''
  }
})
</script>

<template class="min-h-screen flex flex-col">
  <!-- Mobile-First Header -->
  <header class="from-yellow-200 to-yellow-300 bg-gradient-to-r shadow-lg">
    <nav class="mx-auto px-4 py-3 container lg:px-8 sm:px-6">
      <div class="flex flex-col items-center sm:flex-row sm:justify-between space-y-4 sm:space-y-0">
        <!-- Logo Section -->
        <div class="flex-shrink-0">
          <img
            :src="logo"
            class="h-16 w-auto border-2 border-blue-400 rounded-lg p-1 shadow-md lg:h-32 md:h-24 sm:h-20"
            alt="Super Laxmi Logo"
          >
        </div>

        <!-- Title Section -->
        <div class="text-center sm:text-right">
          <h1 class="[text-shadow:_0_2px_0_rgb(0_0_0_/_40%)] font-serif text-lg font-bold tracking-wider text-rose-600 lg:text-3xl md:text-2xl sm:text-xl">
            <a href="/" class="transition-colors duration-300 hover:text-rose-700">
              SUPER LAXMI WIN
            </a>
          </h1>
          <p class="mt-1 text-xs text-rose-500 sm:text-sm">
            Your Lucky Numbers Await
          </p>
        </div>
      </div>
    </nav>
  </header>

  <!-- Main Content -->
  <main class="flex-grow pb-20">
    <RouterView />
  </main>

  <!-- Mobile-First Footer -->
  <footer class="fixed bottom-0 w-full from-gray-800 to-gray-900 bg-gradient-to-r shadow-lg">
    <div class="mx-auto px-4 py-3 container sm:px-6">
      <!-- Footer Content -->
      <div class="flex flex-col items-center sm:flex-row sm:justify-between space-y-2 sm:space-y-0">
        <!-- Logo/Animation -->
        <div class="flex-shrink-0">
          <img
            :src="blaast"
            class="h-auto w-12 md:w-20 sm:w-16"
            alt="Blast Animation"
          >
        </div>

        <!-- Copyright Text -->
        <div class="text-center text-white">
          <p class="text-xs sm:text-sm">
            &copy; 2025
            <a
              href="https://superlaxmiwin.com/"
              target="_blank"
              rel="noreferrer"
              title="Super Laxmi Win"
              class="font-medium text-yellow-300 transition-colors duration-300 hover:text-yellow-200"
            >
              Super Laxmi Win
            </a>
          </p>
          <p class="mt-1 text-xs text-gray-400">
            Play Responsibly | 18+ Only
          </p>
        </div>

        <!-- Dark Mode Toggle (moved from TheFooter.vue) -->
        <div class="flex-shrink-0">
          <button
            class="rounded-lg bg-gray-700 p-2 text-gray-300 transition-all duration-300 hover:bg-gray-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-yellow-500"
            title="Toggle Dark Mode"
            @click="toggleDark()"
          >
            <div class="h-4 w-4 sm:h-5 sm:w-5" :class="isDark ? 'i-carbon-moon' : 'i-carbon-sun'" />
          </button>
        </div>
      </div>
    </div>
  </footer>
</template>
