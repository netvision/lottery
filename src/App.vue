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
  <header v-if="authStore.isLoggedIn">
    <nav class="flex items-center justify-between bg-white px-20 py-10">
      <h1 class="text-xl font-bold text-gray-800">
        <a href="/">My Lottery</a>
      </h1>
      <div class="flex items-center">
        <img :src="authStore.photoURL" class="w-10 rounded-xl"> &nbsp; &nbsp; &nbsp;
        <button class="rounded-md bg-blue-500 px-2 py-1 text-white" @click="authStore.signout">
          Logout
        </button>
      </div>
    </nav>
  </header>
  <main class="flex-grow">
    <RouterView />
  </main>
  <footer class="fixed bottom-0 w-full bg-gray-800 p-4 text-white">
    <div class="mx-auto text-center container">
      &copy; <a
        icon-btn
        rel="noreferrer"
        href="https://rkjkbarasia.com/"
        target="_blank"
        title="RKJK Barasia College"
      >Lottery</a>
    </div>
  </footer>
</template>
