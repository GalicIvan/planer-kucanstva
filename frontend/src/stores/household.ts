import { defineStore } from 'pinia'
import { householdService } from '@/services/household.service'
import type { Household, User } from '@/types'

type HouseholdMemberUser = User & { pivot?: { member_role: 'member' | 'admin'; joined_at: string | null } }

interface HouseholdState {
  currentHousehold: Household | null
  members: HouseholdMemberUser[]
  loading: boolean
  error: string | null
}

export const useHouseholdStore = defineStore('household', {
  state: (): HouseholdState => ({
    currentHousehold: null,
    members: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchHousehold() {
      this.loading = true
      this.error = null
      try {
        const { data } = await householdService.show()
        this.currentHousehold = data
        this.members = data.members || []
      } catch (err: any) {
        this.currentHousehold = null
        this.members = []
        this.error = err.response?.data?.message || 'Kućanstvo nije pronađeno.'
      } finally {
        this.loading = false
      }
    },

    async createHousehold(name: string, description?: string) {
      const { data } = await householdService.create({ name, description })
      this.currentHousehold = data
      this.members = data.members || []
      return data
    },

    async addMember(email: string, memberRole: 'member' | 'admin' = 'member') {
      if (!this.currentHousehold) return
      const { data } = await householdService.addMember(this.currentHousehold.id, email, memberRole)
      this.currentHousehold = data
      this.members = data.members || []
    },

    async removeMember(userId: number) {
      if (!this.currentHousehold) return
      await householdService.removeMember(this.currentHousehold.id, userId)
      this.members = this.members.filter((m) => m.id !== userId)
    },
  },
})
