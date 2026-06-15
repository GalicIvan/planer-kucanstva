<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useDashboardStore } from '@/stores/dashboard'
import StatCard from '@/components/StatCard.vue'
import EmptyState from '@/components/EmptyState.vue'
import BaseBadge from '@/components/BaseBadge.vue'

const dashboard = useDashboardStore()

const categoryLabels: Record<string, string> = {
  utilities: 'Režije',
  groceries: 'Namirnice',
  household: 'Kućanstvo',
  food: 'Hrana / izlasci',
  rent: 'Stanarina',
  transport: 'Prijevoz',
  other: 'Ostalo',
}

function formatCurrency(value: number | string) {
  return `${Number(value).toFixed(2)} €`
}

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('hr-HR')
}

onMounted(() => dashboard.fetchDashboard())
</script>

<template>
  <div class="space-y-6">
    <div v-if="dashboard.error" class="app-card text-accent text-sm">{{ dashboard.error }}</div>

    <div v-if="dashboard.data?.message" class="app-card text-sm text-muted">
      {{ dashboard.data.message }}
    </div>

    <template v-if="dashboard.data && !dashboard.data.message">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          label="Troškovi ovaj mjesec"
          :value="formatCurrency(dashboard.data.total_expenses_this_month)"
          dark
        />
        <StatCard label="Ja dugujem" :value="formatCurrency(dashboard.data.i_owe)" hint="Ukupno drugima u kućanstvu" />
        <StatCard label="Drugi mi duguju" :value="formatCurrency(dashboard.data.owed_to_me)" hint="Ukupno od drugih" />
        <StatCard label="Otvoreni zadaci" :value="dashboard.data.open_tasks_count" hint="Status: na čekanju" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="app-card">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-ink">Posljednji troškovi</h2>
            <RouterLink to="/expenses" class="text-sm text-accent font-medium">Svi troškovi</RouterLink>
          </div>
          <EmptyState v-if="dashboard.data.latest_expenses.length === 0" title="Nema unesenih troškova" />
          <ul v-else class="divide-y divide-surface">
            <li v-for="expense in dashboard.data.latest_expenses" :key="expense.id" class="py-2.5 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-ink">{{ expense.title }}</p>
                <p class="text-xs text-muted">
                  {{ categoryLabels[expense.category] ?? expense.category }} · {{ formatDate(expense.expense_date) }}
                </p>
              </div>
              <p class="text-sm font-semibold text-ink">{{ formatCurrency(expense.amount) }}</p>
            </li>
          </ul>
        </div>

        <div class="app-card">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-ink">Posljednji zadaci</h2>
            <RouterLink to="/tasks" class="text-sm text-accent font-medium">Svi zadaci</RouterLink>
          </div>
          <EmptyState v-if="dashboard.data.latest_tasks.length === 0" title="Nema zadataka" />
          <ul v-else class="divide-y divide-surface">
            <li v-for="task in dashboard.data.latest_tasks" :key="task.id" class="py-2.5 flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-medium text-ink">{{ task.title }}</p>
                <p class="text-xs text-muted">
                  {{ task.assignee?.name ?? 'Nedodijeljeno' }}
                  <span v-if="task.due_date"> · rok {{ formatDate(task.due_date) }}</span>
                </p>
              </div>
              <BaseBadge :variant="task.status === 'done' ? 'success' : 'pending'">
                {{ task.status === 'done' ? 'Završeno' : 'Na čekanju' }}
              </BaseBadge>
            </li>
          </ul>
        </div>
      </div>
    </template>

    <div v-else-if="dashboard.loading" class="app-card text-center text-muted py-10">Učitavanje...</div>
  </div>
</template>
