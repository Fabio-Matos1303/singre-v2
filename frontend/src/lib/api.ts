import axios from 'axios'

// Prefer same-origin relative baseURL so /api is proxied by frontend Nginx
const api = axios.create({
  baseURL: (import.meta as any).env?.VITE_API_URL || '',
  withCredentials: false,
})

// Attach Authorization header if token exists
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers = config.headers || {}
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Redirect to login on 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      localStorage.removeItem('auth_token')
      if (typeof window !== 'undefined' && !location.pathname.startsWith('/login')) {
        location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
