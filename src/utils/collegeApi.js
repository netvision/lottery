import axios from 'axios'
import { getAuth } from 'firebase/auth'

// Create axios instance for college API protected calls
const collegeProtectedAPI = axios.create({
  baseURL: 'https://collegeapi.netserve.in',
  timeout: 10000,
})

// Request interceptor to add Firebase auth token to all requests
collegeProtectedAPI.interceptors.request.use(
  async (config) => {
    try {
      const auth = getAuth()
      const user = auth.currentUser

      if (user) {
        // Get the Firebase ID token
        const token = await user.getIdToken()

        // Add the token to the Authorization header
        config.headers.Authorization = `Bearer ${token}`
      }
      else {
        // If no user is authenticated, reject the request
        throw new Error('User not authenticated')
      }

      return config
    }
    catch (error) {
      console.error('Error getting auth token:', error)
      return Promise.reject(error)
    }
  },
  (error) => {
    return Promise.reject(error)
  },
)

// Response interceptor to handle auth errors
collegeProtectedAPI.interceptors.response.use(
  (response) => {
    return response
  },
  async (error) => {
    const originalRequest = error.config

    // If we get a 401 (Unauthorized) response and haven't already retried
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true

      try {
        const auth = getAuth()
        const user = auth.currentUser

        if (user) {
          // Force refresh the token
          const token = await user.getIdToken(true)
          originalRequest.headers.Authorization = `Bearer ${token}`

          // Retry the original request with the new token
          return collegeProtectedAPI(originalRequest)
        }
      }
      catch (refreshError) {
        console.error('Error refreshing token:', refreshError)
        // Redirect to login page or handle auth failure
        window.location.href = '/login'
      }
    }

    return Promise.reject(error)
  },
)

export default collegeProtectedAPI
