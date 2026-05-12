import { defineStore } from 'pinia'
import { authApi } from '@/api/authApi'
import { TokenIntrospector } from '@/auth/TokenIntrospector'

type User = {
  id: string
  email: string
  name: string
}

const TOKEN_KEY = 'jwt_token'

function loadStoredToken(): string | null {
  const token = localStorage.getItem(TOKEN_KEY)
  if (!token) return null

  if (TokenIntrospector.isExpired(token)) {
    localStorage.removeItem(TOKEN_KEY)
    return null
  }

  return token
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: loadStoredToken(),
    loading: false,
    error: null as string | null,
  }),

  getters: {
    isAuthenticated: (state: { token: string | null }): boolean => {
      return !!state.token && !TokenIntrospector.isExpired(state.token)
    },
  },

  actions: {
    async login(email: string, password: string) {
      this.loading = true
      this.error   = null

      try {
        const data = await authApi.login(email, password)

        this.token = data.token
        this.user  = data.user

        localStorage.setItem(TOKEN_KEY, data.token)
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
        return await authApi.register(email, password, name)
      } catch (e: any) {
        this.error = e.response?.data?.error ?? 'Error al registrarse'
        throw e
      } finally {
        this.loading = false
      }
    },

    clearSession() {
      this.token = null
      this.user  = null
      localStorage.removeItem(TOKEN_KEY)
    },

    logout() {
      this.clearSession()
      window.location.href = '/login'
    },
  },
})
