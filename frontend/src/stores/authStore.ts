import { defineStore } from 'pinia'
import api from '@/api/axios'

type User = {
  id: string
  email: string
  name: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: localStorage.getItem('jwt_token') as string | null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(email: string, password: string) {
      this.loading = true
      this.error   = null

      try {
        const { data } = await api.post('/auth/login', { email, password })

        this.token = data.token
        this.user  = data.user

        localStorage.setItem('jwt_token', data.token)

      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al iniciar sesión'
        throw e
      } finally {
        this.loading = false
      }
    },

    async register(email: string, password: string, name: string) {
      this.loading = true
      this.error   = null

      try {
        const { data } = await api.post('/auth/register', { email, password, name })
        return data
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al registrarse'
        throw e
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.token = null
      this.user  = null
      localStorage.removeItem('jwt_token')
      window.location.href = '/login'
    }
  }
})