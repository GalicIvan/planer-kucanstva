import api from './api'
import type { Receipt } from '@/types'

export const receiptService = {
  list() {
    return api.get<Receipt[]>('/receipts')
  },
  upload(expenseId: number, file: File) {
    const formData = new FormData()
    formData.append('expense_id', String(expenseId))
    formData.append('file', file)
    return api.post<Receipt>('/receipts', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },
  remove(id: number) {
    return api.delete(`/receipts/${id}`)
  },
}
