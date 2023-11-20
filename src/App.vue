<script setup>
import { getAuth, onAuthStateChanged } from 'firebase/auth'
import { useAuthStore } from './stores/authStore'

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
    <nav class="flex bg-yellow-100 px-20 py-3 align-bottom">
      <div>
        <img src="laxmi-logo.png" class="h-32 border border-2 border-blue-400 p-2">
      </div>
      <div class="self-end pl-5">
        <h1 class="text-3xl font-bold tracking-widest text-rose-600">
          <a href="/">SUPER<br>LAXMI</a>
        </h1>
      </div>
    </nav>
  </header>
  <main class="custom-bg flex-grow">
    <RouterView />
  </main>
  <footer class="fixed bottom-0 w-full bg-gray-800 p-4 text-white">
    <div class="mx-auto text-center container">
      &copy; <a
        icon-btn
        rel="noreferrer"
        href="https://superlaxmiwin.com/"
        target="_blank"
        title="Super Laxmi"
      >Super Laxmi</a>
    </div>
  </footer>
</template>

<style type="text/css">
  .custom-bg{
    background-image: url('superlaxmilogo.png');
    background-size: cover;
    background-position: center;
  }
</style>
