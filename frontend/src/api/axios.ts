import axios from 'axios'
import { TokenIntrospector } from '@/auth/TokenIntrospector'

const TOKEN_KEY = 'jwt_token'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)

  if (token && TokenIntrospector.isExpired(token)) {
    localStorage.removeItem(TOKEN_KEY)
    window.location.href = '/login'
    return Promise.reject(new axios.Cancel('Token expirado'))
  }

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (axios.isCancel(error)) return Promise.reject(error)

    const isAuthEndpoint = error.config?.url?.startsWith('/auth/')
    if (error.response?.status === 401 && !isAuthEndpoint) {
      localStorage.removeItem(TOKEN_KEY)
      window.location.href = '/login'
    }

    return Promise.reject(error)
  }
)

export default api
