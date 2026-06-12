<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { expenseService } from '@/services/expense.service'
import { useHouseholdStore } from '@/stores/household'
import { useExpenseStore } from '@/stores/expense'
import type { Expense } from '@/types'
import BaseBadge from '@/components/BaseBadge.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseModal from '@/components/BaseModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ExpenseForm from '@/components/ExpenseForm.vue'

const route = useRoute()
const router = useRouter()
const household = useHouseholdStore()
const expenseStore = useExpenseStore()

const expense = ref<Expense | null>(null)
const loading = ref(true)
const error = ref('')

const categoryLabels: Record<string, string> = {
  utilities: 'Režije',
  groceries: 'Namirnice',
  household: 'Kućanstvo',
  food: 'Hrana / izlasci',
  rent: 'Stanarina',
  transport: 'Prijevoz',
  other: 'Ostalo',
}

const storageBaseUrl = computed(() => {
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  return apiUrl.replace(/\/api\/?$/, '')
})

function fileUrl(path: string) {
  return `${storageBaseUrl.value}/storage/${path}`
}

function formatCurrency(value: number | string) {
  return `${Number(value).toFixed(2)} €`
}

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('hr-HR')
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const id = Number(route.params.id)
    const { data } = await expenseService.get(id)
    expense.value = data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Trošak nije pronađen.'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (!household.currentHousehold) {
    await household.fetchHousehold()
  }
  await load()
})

// Edit modal
const formOpen = ref(false)
const formError = ref('')

async function onFormSubmit(formData: FormData) {
  if (!expense.value) return
  formError.value = ''
  try {
    const { data } = await expenseService.update(expense.value.id, formData)
    expense.value = data
    formOpen.value = false
  } catch (err: any) {
    const errors = err.response?.data?.errors
    formError.value = errors
      ? Object.values(errors).flat().join(' ')
      : err.response?.data?.message || 'Greška prilikom spremanja troška.'
  }
}

// Delete
const confirmDeleteOpen = ref(false)

async function onConfirmDelete() {
  if (!expense.value) return
  await expenseStore.deleteExpense(expense.value.id)
  router.push('/expenses')
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <button class="text-sm text-accent font-medium" @click="router.push('/expenses')">← Svi troškovi</button>
    </div>

    <div v-if="loading" class="app-card text-center text-muted py-10">Učitavanje...</div>
    <div v-else-if="error" class="app-card text-accent text-sm">{{ error }}</div>

    <template v-else-if="expense">
      <div class="app-card">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold text-ink">{{ expense.title }}</h2>
            <p class="text-sm text-muted mt-1">
              {{ categoryLabels[expense.category] ?? expense.category }} · {{ formatDate(expense.expense_date) }}
            </p>
            <p v-if="expense.description" class="text-sm text-ink mt-3">{{ expense.description }}</p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-2xl font-semibold text-ink">{{ formatCurrency(expense.amount) }}</p>
            <p class="text-sm text-muted mt-1">Platio: {{ expense.payer?.name ?? '—' }}</p>
          </div>
        </div>

        <div class="flex gap-2 mt-4">
          <BaseButton variant="secondary" @click="formOpen = true">Uredi</BaseButton>
          <BaseButton variant="danger" @click="confirmDeleteOpen = true">Obriši</BaseButton>
        </div>
      </div>

      <div class="app-card">
        <h3 class="font-semibold text-ink mb-3">Podjela troška</h3>
        <ul v-if="expense.shares && expense.shares.length > 0" class="divide-y divide-surface">
          <li v-for="share in expense.shares" :key="share.id" class="py-2.5 flex items-center justify-between">
            <span class="text-sm text-ink">{{ share.user?.name ?? '—' }}</span>
            <div class="flex items-center gap-3">
              <span class="text-sm font-medium text-ink">{{ formatCurrency(share.amount) }}</span>
              <BaseBadge :variant="share.is_settled ? 'success' : 'pending'">
                {{ share.is_settled ? 'Podmireno' : 'Nepodmireno' }}
              </BaseBadge>
            </div>
          </li>
        </ul>
        <p v-else class="text-sm text-muted">Trošak nije podijeljen s drugim članovima.</p>
      </div>

      <div class="app-card">
        <h3 class="font-semibold text-ink mb-3">Računi</h3>
        <ul v-if="expense.receipts && expense.receipts.length > 0" class="space-y-2">
          <li v-for="receipt in expense.receipts" :key="receipt.id" class="flex items-center justify-between text-sm">
            <span class="text-ink">{{ receipt.original_name }}</span>
            <a :href="fileUrl(receipt.file_path)" target="_blank" class="text-accent font-medium">Otvori</a>
          </li>
        </ul>
        <p v-else class="text-sm text-muted">Za ovaj trošak nije učitan nijedan račun.</p>
      </div>
    </template>

    <BaseModal v-model="formOpen" title="Uredi trošak">
      <p v-if="formError" class="text-sm text-accent mb-3">{{ formError }}</p>
      <ExpenseForm
        v-if="expense"
        :expense="expense"
        :members="household.members"
        @submit="onFormSubmit"
        @cancel="formOpen = false"
      />
    </BaseModal>

    <ConfirmDialog
      v-model="confirmDeleteOpen"
      title="Obriši trošak"
      message="Jeste li sigurni da želite obrisati ovaj trošak? Ova radnja se ne može poništiti."
      confirm-label="Obriši"
      @confirm="onConfirmDelete"
    />
  </div>
</template>
