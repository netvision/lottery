/* eslint-disable unused-imports/no-unused-vars */
/* eslint-disable no-alert */

import { defineStore } from 'pinia'
import { getAuth, signInWithEmailAndPassword, signOut } from 'firebase/auth'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    isLoggedIn: false,
    name: '',
    email: '',
    uid: '',
    photoURL: '',
    token: null,
    user: null,
  }),
  getters: {
    isAuthenticated: state => !!state.user,
  },
  actions: {
    async signIn(email, password) {
      const auth = getAuth()
      try {
        const result = await signInWithEmailAndPassword(auth, email, password)
        const user = result.user

        // Get the ID token
        const token = await user.getIdToken()

        // Update store state
        this.isLoggedIn = true
        this.name = user.displayName
        this.uid = user.uid
        this.email = user.email
        this.photoURL = user.photoURL
        this.token = token
        this.user = user

        this.$router.push('/admin')
      }
      catch (error) {
        const errorCode = error.code
        this.errorMessage = error.message
        alert(this.errorMessage)
        throw error
      }
    },

    async signout() {
      const auth = getAuth()
      try {
        await signOut(auth)

        // Clear store state
        this.isLoggedIn = false
        this.name = ''
        this.uid = ''
        this.email = ''
        this.photoURL = ''
        this.token = null
        this.user = null

        alert('logged out')
        this.$router.push('/')
      }
      catch (error) {
        const errorCode = error.code
        this.errorMessage = error.message
        alert(this.errorMessage)
        throw error
      }
    },

    async getToken() {
      const auth = getAuth()
      const user = auth.currentUser

      if (user) {
        try {
          const token = await user.getIdToken()
          this.token = token
          return token
        }
        catch (error) {
          console.error('Error getting token:', error)
          return null
        }
      }
      return null
    },

    setUser(user) {
      if (user) {
        this.isLoggedIn = true
        this.name = user.displayName
        this.uid = user.uid
        this.email = user.email
        this.photoURL = user.photoURL
        this.user = user
      }
      else {
        this.isLoggedIn = false
        this.name = ''
        this.uid = ''
        this.email = ''
        this.photoURL = ''
        this.token = null
        this.user = null
      }
    },
  },
})
