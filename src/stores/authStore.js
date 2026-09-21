import axios from 'axios'
import { defineStore } from 'pinia'

const tokenKey = 'lottery_access_token'
const usernameKey = 'lottery_admin_username'
const apiBase = import.meta.env.VITE_API_BASE_URL || 'https://superlaxmi.netserve.in'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem(tokenKey),
    username: localStorage.getItem(usernameKey) || '',
    errorMessage: '',
  }),
  getters: {
    isLoggedIn: state => Boolean(state.token),
    isAuthenticated: state => Boolean(state.token),
  },
  actions: {
    async signIn(username, password) {
      this.errorMessage = ''
      try {
        const response = await axios.post(`${apiBase}/auth/login`, { username, password })
        this.token = response.data.access_token
        this.username = username
        localStorage.setItem(tokenKey, this.token)
        localStorage.setItem(usernameKey, username)
        this.$router.push('/admin')
      }
      catch (error) {
        this.errorMessage = error.response?.data?.detail || 'Login failed'
        throw error
      }
    },
    signout() {
      this.token = null
      this.username = ''
      this.errorMessage = ''
      localStorage.removeItem(tokenKey)
      localStorage.removeItem(usernameKey)
      this.$router.push('/login')
    },
    getToken() {
      return this.token
    },
    async changePassword(currentPassword, newPassword) {
      const response = await axios.post(`${apiBase}/auth/change-password`, {
        current_password: currentPassword,
        new_password: newPassword,
      }, {
        headers: { Authorization: `Bearer ${this.token}` },
      })
      return response
    },
  },
})
