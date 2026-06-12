import api from './api'
import type { ShoppingItem } from '@/types'

export interface ShoppingItemPayload {
  name: string
  quantity?: number
  is_purchased?: boolean
}

export const shoppingService = {
  list(filters: { is_purchased?: boolean } = {}) {
    return api.get<ShoppingItem[]>('/shopping-items', { params: filters })
  },
  create(payload: ShoppingItemPayload) {
    return api.post<ShoppingItem>('/shopping-items', payload)
  },
  update(id: number, payload: Partial<ShoppingItemPayload>) {
    return api.put<ShoppingItem>(`/shopping-items/${id}`, payload)
  },
  markPurchased(id: number, is_purchased: boolean) {
    return api.patch<ShoppingItem>(`/shopping-items/${id}/purchased`, { is_purchased })
  },
  remove(id: number) {
    return api.delete(`/shopping-items/${id}`)
  },
}
