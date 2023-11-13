/* eslint-disable unused-imports/no-unused-vars */
/* eslint-disable no-alert */

import { defineStore } from 'pinia'
import { getAuth, signInWithEmailAndPassword, signOut } from 'firebase/auth'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    isLoggedIn: true,
    name: '',
    email: '',
    uid: '',
    photoURL: '',
  }),
  getters: {},
  actions: {
    signIn(email, password) {
      const auth = getAuth()
      signInWithEmailAndPassword(auth, email, password).then((result) => {
        const user = result.user
        this.$router.push('/admin')
      }).catch((error) => {
        const errorCode = error.code
        this.errorMessage = error.message
        alert(this.errorMessage)
      })
    },

    signout() {
      const auth = getAuth()
      signOut(auth)
        .then(() => {
          alert('logged out')
          this.$router.push('/')
        })
        .catch((error) => {
          const errorCode = error.code
          this.errorMessage = error.message
          alert(this.errorMessage)
        })
    },
  },
})
