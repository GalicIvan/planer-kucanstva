import { defineStore } from 'pinia'
import { dashboardService } from '@/services/dashboard.service'
import type { DashboardData } from '@/types'

interface DashboardState {
  data: DashboardData | null
  loading: boolean
  error: string | null
}

export const useDashboardStore = defineStore('dashboard', {
  state: (): DashboardState => ({
    data: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchDashboard() {
      this.loading = true
      this.error = null
      try {
        const { data } = await dashboardService.get()
        this.data = data
        return data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Greška prilikom dohvaćanja podataka nadzorne ploče.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async refresh() {
      return this.fetchDashboard()
    },
  },
})
