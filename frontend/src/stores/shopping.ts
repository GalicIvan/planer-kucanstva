import { defineStore } from 'pinia'
import { shoppingService, type ShoppingItemPayload } from '@/services/shopping.service'
import { useDashboardStore } from './dashboard'
import type { ShoppingItem } from '@/types'

interface ShoppingState {
  items: ShoppingItem[]
  loading: boolean
  error: string | null
}

export const useShoppingStore = defineStore('shopping', {
  state: (): ShoppingState => ({
    items: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchItems(filters: { is_purchased?: boolean } = {}) {
      this.loading = true
      this.error = null
      try {
        const { data } = await shoppingService.list(filters)
        this.items = data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Greška prilikom dohvaćanja popisa za kupnju.'
      } finally {
        this.loading = false
      }
    },

    async createItem(payload: ShoppingItemPayload) {
      const { data } = await shoppingService.create(payload)
      this.items.unshift(data)
      await useDashboardStore().refresh().catch(() => undefined)
      return data
    },

    async updateItem(id: number, payload: Partial<ShoppingItemPayload>) {
      const { data } = await shoppingService.update(id, payload)
      const idx = this.items.findIndex((i) => i.id === id)
      if (idx !== -1) this.items[idx] = data
      await useDashboardStore().refresh().catch(() => undefined)
      return data
    },

    async markPurchased(id: number, isPurchased: boolean) {
      const { data } = await shoppingService.markPurchased(id, isPurchased)
      const idx = this.items.findIndex((i) => i.id === id)
      if (idx !== -1) this.items[idx] = data
      await useDashboardStore().refresh().catch(() => undefined)
      return data
    },

    async deleteItem(id: number) {
      await shoppingService.remove(id)
      this.items = this.items.filter((i) => i.id !== id)
      await useDashboardStore().refresh().catch(() => undefined)
    },
  },
})
