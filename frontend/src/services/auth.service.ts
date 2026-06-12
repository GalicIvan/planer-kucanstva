import api from './api'
import type { User } from '@/types'

export interface AuthResponse {
  user: User
  token: string
}

export const authService = {
  login(email: string, password: string) {
    return api.post<AuthResponse>('/auth/login', { email, password })
  },
  register(name: string, email: string, password: string, password_confirmation: string) {
    return api.post<AuthResponse>('/auth/register', { name, email, password, password_confirmation })
  },
  logout() {
    return api.post('/auth/logout')
  },
  me() {
    return api.get<User>('/auth/me')
  },
}
