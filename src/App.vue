<!-- eslint-disable vue/html-self-closing -->
<script setup>
import { getAuth, onAuthStateChanged } from 'firebase/auth'
import { useAuthStore } from './stores/authStore'
import logo from '~/assets/logo.png'
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
  <header>
    <nav class="flex justify-center bg-yellow-200 px-20 py-3 align-bottom">
      <div>
        <img :src="logo" class="h-60 border-2 border-blue-400 p-2">
      </div>
      <div class="self-end pl-5">
        <h1 class="[text-shadow:_0_2px_0_rgb(0_0_0_/_40%)] font-serif text-3xl font-bold tracking-widest text-rose-600">
          <a href="/">SUPER<br>LAXMI<br>WIN</a>
        </h1>
      </div>
    </nav>
  </header>
  <main class="flex-grow">
    <RouterView />
  </main>
  <footer class="fixed bottom-0 w-full bg-gray-800 p-4 text-white">
    <div class="mx-auto text-center">
      <img :src="blaast" class="mx-auto w-24">
    </div>
    <div class="mx-auto text-center container">
      &copy; <a
        icon-btn
        rel="noreferrer"
        href="https://superlaxmiwin.com/"
        target="_blank"
        title="Super Laxmi Win"
      >Super Laxmi Win</a>
    </div>
  </footer>
</template>
