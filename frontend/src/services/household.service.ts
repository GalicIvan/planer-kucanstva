import api from './api'
import type { Household, User } from '@/types'

export const householdService = {
  show() {
    return api.get<Household>('/household')
  },
  create(payload: { name: string; description?: string }) {
    return api.post<Household>('/household', payload)
  },
  update(id: number, payload: { name?: string; description?: string }) {
    return api.put<Household>(`/household/${id}`, payload)
  },
  members(id: number) {
    return api.get<User[]>(`/household/${id}/members`)
  },
  addMember(id: number, email: string, member_role: 'member' | 'admin' = 'member') {
    return api.post<Household>(`/household/${id}/members`, { email, member_role })
  },
  removeMember(householdId: number, userId: number) {
    return api.delete(`/household/${householdId}/members/${userId}`)
  },
}
