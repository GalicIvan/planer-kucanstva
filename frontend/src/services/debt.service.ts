import api from './api'
import type { DebtsResponse, ExpenseShare } from '@/types'

export const debtService = {
  list() {
    return api.get<DebtsResponse>('/debts')
  },
  settle(shareId: number) {
    return api.patch<ExpenseShare>(`/debts/${shareId}/settle`)
  },
}
