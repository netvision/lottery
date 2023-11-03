<!-- eslint-disable no-console -->
<script setup>
import { useAuthStore } from '~/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()
const email = ref('')
const password = ref('')

function signIn() {
  authStore.signIn(email.value, password.value)
}

function signOut() {
  authStore.signout()
}
</script>

<template>
  <div class="min-h-screen flex flex-col justify-center bg-gray-100 py-6 sm:py-12">
    <div class="relative py-3 sm:mx-auto sm:max-w-xl">
      <div
        class="absolute inset-0 transform from-blue-300 to-blue-600 bg-gradient-to-r shadow-lg -skew-y-6 sm:skew-y-0 sm:rounded-3xl sm:-rotate-6"
      />
      <div class="relative bg-white px-4 py-10 shadow-lg sm:rounded-3xl sm:p-20">
        <div class="mx-auto max-w-md">
          <div>
            <h1 class="text-2xl font-semibold">
              Please Login
            </h1>
          </div>
          <div class="divide-y divide-gray-200">
            <div class="py-8 text-base leading-6 text-gray-700 space-y-4 sm:text-lg sm:leading-7">
              <div class="relative">
                <input id="email" v-model="email" autocomplete="off" name="email" type="text" class="peer focus:borer-rose-600 h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none placeholder-transparent" placeholder="Email address">
                <label for="email" class="peer-placeholder-shown:text-gray-440 absolute left-0 text-sm text-gray-600 transition-all -top-3.5 peer-placeholder-shown:top-2 peer-focus:text-sm peer-placeholder-shown:text-base peer-focus:text-gray-600 peer-focus:-top-3.5">Email Address</label>
              </div>
              <div class="relative">
                <input id="password" v-model="password" autocomplete="off" name="password" type="password" class="peer focus:borer-rose-600 h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none placeholder-transparent" placeholder="Password">
                <label for="password" class="peer-placeholder-shown:text-gray-440 absolute left-0 text-sm text-gray-600 transition-all -top-3.5 peer-placeholder-shown:top-2 peer-focus:text-sm peer-placeholder-shown:text-base peer-focus:text-gray-600 peer-focus:-top-3.5">Password</label>
              </div>
              <div class="relative">
                <button class="rounded-md bg-blue-500 px-2 py-1 text-white" @click="signIn">
                  Submit
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <button
    v-if="!authStore.isLoggedIn"
    class="m-3 text-sm btn"
    @click="signInGoogle"
  >
    Log in with Google
  </button>
  <button
    v-else
    class="m-3 text-sm btn"
    @click="signOut"
  >
    Log out
  </button>
</template>
