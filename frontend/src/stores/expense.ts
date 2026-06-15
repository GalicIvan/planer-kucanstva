import { defineStore } from 'pinia'
import { expenseService, type ExpenseFilters } from '@/services/expense.service'
import { useDashboardStore } from './dashboard'
import type { Expense } from '@/types'

interface ExpenseState {
  expenses: Expense[]
  filters: ExpenseFilters
  currentPage: number
  lastPage: number
  total: number
  loading: boolean
  error: string | null
}

export const useExpenseStore = defineStore('expense', {
  state: (): ExpenseState => ({
    expenses: [],
    filters: {},
    currentPage: 1,
    lastPage: 1,
    total: 0,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchExpenses(filters: ExpenseFilters = {}) {
      this.loading = true
      this.error = null
      this.filters = { ...this.filters, ...filters }
      try {
        const { data } = await expenseService.list(this.filters)
        this.expenses = data.data
        this.currentPage = data.current_page
        this.lastPage = data.last_page
        this.total = data.total
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Greška prilikom dohvaćanja troškova.'
      } finally {
        this.loading = false
      }
    },

    async createExpense(payload: FormData) {
      const { data } = await expenseService.create(payload)
      this.expenses.unshift(data)
      await useDashboardStore().refresh().catch(() => undefined)
      return data
    },

    async updateExpense(id: number, payload: FormData) {
      const { data } = await expenseService.update(id, payload)
      const idx = this.expenses.findIndex((e) => e.id === id)
      if (idx !== -1) this.expenses[idx] = data
      await useDashboardStore().refresh().catch(() => undefined)
      return data
    },

    async deleteExpense(id: number) {
      await expenseService.remove(id)
      this.expenses = this.expenses.filter((e) => e.id !== id)
      await useDashboardStore().refresh().catch(() => undefined)
    },
  },
})
