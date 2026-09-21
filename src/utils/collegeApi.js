import axios from 'axios'

const collegeProtectedAPI = axios.create({
  baseURL: 'https://collegeapi.netserve.in',
  timeout: 10000,
})

collegeProtectedAPI.interceptors.request.use((config) => {
  const token = localStorage.getItem('lottery_access_token')
  if (token)
    config.headers.Authorization = `Bearer ${token}`
  return config
})

collegeProtectedAPI.interceptors.response.use(
  response => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('lottery_access_token')
      localStorage.removeItem('lottery_admin_username')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  },
)

export default collegeProtectedAPI
