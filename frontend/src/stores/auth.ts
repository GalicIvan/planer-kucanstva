import { defineStore } from 'pinia'
import { authService } from '@/services/auth.service'
import type { User, UserRole } from '@/types'

interface AuthState {
  user: User | null
  token: string | null
  loading: boolean
  error: string | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: JSON.parse(localStorage.getItem('pk_user') || 'null'),
    token: localStorage.getItem('pk_token'),
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    role: (state): UserRole | null => state.user?.role ?? null,
  },

  actions: {
    hasRole(...roles: UserRole[]): boolean {
      return !!this.user && roles.includes(this.user.role)
    },

    persist(user: User, token: string) {
      this.user = user
      this.token = token
      localStorage.setItem('pk_token', token)
      localStorage.setItem('pk_user', JSON.stringify(user))
    },

    async login(email: string, password: string) {
      this.loading = true
      this.error = null
      try {
        const { data } = await authService.login(email, password)
        this.persist(data.user, data.token)
        return true
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Greška prilikom prijave.'
        return false
      } finally {
        this.loading = false
      }
    },

    async register(name: string, email: string, password: string, passwordConfirmation: string) {
      this.loading = true
      this.error = null
      try {
        const { data } = await authService.register(name, email, password, passwordConfirmation)
        this.persist(data.user, data.token)
        return true
      } catch (err: any) {
        const messages = err.response?.data?.errors
        this.error = messages
          ? Object.values(messages).flat().join(' ')
          : err.response?.data?.message || 'Greška prilikom registracije.'
        return false
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await authService.logout()
      } catch {
        // ignore network errors on logout
      }
      this.user = null
      this.token = null
      localStorage.removeItem('pk_token')
      localStorage.removeItem('pk_user')
    },

    async fetchMe() {
      try {
        const { data } = await authService.me()
        this.user = data
        localStorage.setItem('pk_user', JSON.stringify(data))
      } catch {
        this.user = null
        this.token = null
        localStorage.removeItem('pk_token')
        localStorage.removeItem('pk_user')
      }
    },
  },
})
