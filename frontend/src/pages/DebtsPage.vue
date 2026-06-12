<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { debtService } from '@/services/debt.service'
import type { DebtsResponse } from '@/types'
import BaseBadge from '@/components/BaseBadge.vue'
import BaseButton from '@/components/BaseButton.vue'
import EmptyState from '@/components/EmptyState.vue'

const data = ref<DebtsResponse | null>(null)
const loading = ref(true)
const error = ref('')
const settling = ref<number | null>(null)

function formatCurrency(value: number | string) {
  return `${Number(value).toFixed(2)} €`
}

function formatDate(value?: string) {
  if (!value) return ''
  return new Date(value).toLocaleDateString('hr-HR')
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data: res } = await debtService.list()
    data.value = res
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Greška prilikom dohvaćanja dugova.'
  } finally {
    loading.value = false
  }
}

async function settle(shareId: number) {
  settling.value = shareId
  try {
    await debtService.settle(shareId)
    await load()
  } finally {
    settling.value = null
  }
}

onMounted(load)
</script>

<template>
  <div class="space-y-6">
    <div v-if="loading" class="app-card text-center text-muted py-10">Učitavanje...</div>
    <div v-else-if="error" class="app-card text-accent text-sm">{{ error }}</div>

    <template v-else-if="data">
      <div class="app-card">
        <h2 class="font-semibold text-ink mb-3">Tko kome duguje</h2>
        <EmptyState v-if="data.balances.length === 0" title="Nema otvorenih dugova" description="Svi troškovi su podmireni." />
        <ul v-else class="divide-y divide-surface">
          <li v-for="balance in data.balances" :key="balance.user_id" class="py-2.5 flex items-center justify-between">
            <span class="text-sm text-ink">{{ balance.user_name }}</span>
            <span class="text-sm font-semibold" :class="balance.net_amount >= 0 ? 'text-ink' : 'text-accent'">
              <template v-if="balance.net_amount > 0">duguje vama {{ formatCurrency(balance.net_amount) }}</template>
              <template v-else-if="balance.net_amount < 0">vi dugujete {{ formatCurrency(-balance.net_amount) }}</template>
              <template v-else>podmireno</template>
            </span>
          </li>
        </ul>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="app-card">
          <h3 class="font-semibold text-ink mb-3">Ja dugujem</h3>
          <EmptyState v-if="data.owed_by_me.length === 0" title="Nemate nepodmirenih dugova" />
          <ul v-else class="divide-y divide-surface">
            <li v-for="share in data.owed_by_me" :key="share.id" class="py-2.5 flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-medium text-ink">{{ share.expense?.title }}</p>
                <p class="text-xs text-muted">
                  Platio: {{ share.expense?.payer?.name }} · {{ formatDate(share.expense?.expense_date) }}
                </p>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span class="text-sm font-semibold text-ink">{{ formatCurrency(share.amount) }}</span>
                <BaseButton variant="secondary" :disabled="settling === share.id" @click="settle(share.id)">
                  {{ settling === share.id ? 'Spremanje...' : 'Označi plaćeno' }}
                </BaseButton>
              </div>
            </li>
          </ul>
        </div>

        <div class="app-card">
          <h3 class="font-semibold text-ink mb-3">Drugi mi duguju</h3>
          <EmptyState v-if="data.owed_to_me.length === 0" title="Nitko vam ne duguje" />
          <ul v-else class="divide-y divide-surface">
            <li v-for="share in data.owed_to_me" :key="share.id" class="py-2.5 flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-medium text-ink">{{ share.expense?.title }}</p>
                <p class="text-xs text-muted">
                  Duguje: {{ share.user?.name }} · {{ formatDate(share.expense?.expense_date) }}
                </p>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span class="text-sm font-semibold text-ink">{{ formatCurrency(share.amount) }}</span>
                <BaseButton variant="secondary" :disabled="settling === share.id" @click="settle(share.id)">
                  {{ settling === share.id ? 'Spremanje...' : 'Označi primljeno' }}
                </BaseButton>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </template>
  </div>
</template>
