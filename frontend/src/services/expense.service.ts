import api from './api'
import type { Expense, PaginatedResponse } from '@/types'

export interface ExpenseFilters {
  search?: string
  category?: string | string[]
  paid_by_user_id?: number | string
  date_from?: string
  date_to?: string
  page?: number
  per_page?: number
}

export const expenseService = {
  list(filters: ExpenseFilters = {}) {
    return api.get<PaginatedResponse<Expense>>('/expenses', { params: filters })
  },
  get(id: number) {
    return api.get<Expense>(`/expenses/${id}`)
  },
  create(payload: FormData) {
    return api.post<Expense>('/expenses', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },
  update(id: number, payload: FormData) {
    // Laravel doesn't parse multipart PUT bodies well from some clients,
    // so we POST with _method=PUT spoofing when a file is attached.
    payload.append('_method', 'PUT')
    return api.post<Expense>(`/expenses/${id}`, payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },
  remove(id: number) {
    return api.delete(`/expenses/${id}`)
  },
}
