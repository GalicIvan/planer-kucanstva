import api from './api'
import type { User, UserRole } from '@/types'

export const userService = {
  list(params: { search?: string; role?: string } = {}) {
    return api.get<User[]>('/users', { params })
  },
  get(id: number) {
    return api.get<User>(`/users/${id}`)
  },
  update(id: number, payload: { name?: string; email?: string }) {
    return api.put<User>(`/users/${id}`, payload)
  },
  changeRole(id: number, role: UserRole) {
    return api.patch<User>(`/users/${id}/role`, { role })
  },
  toggleActive(id: number) {
    return api.patch<User>(`/users/${id}/deactivate`)
  },
}
