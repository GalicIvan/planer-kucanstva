<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useExpenseStore } from '@/stores/expense'
import { useHouseholdStore } from '@/stores/household'
import BaseInput from '@/components/BaseInput.vue'
import BaseSelect from '@/components/BaseSelect.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseModal from '@/components/BaseModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import DataTable from '@/components/DataTable.vue'
import ExpenseForm from '@/components/ExpenseForm.vue'
import EmptyState from '@/components/EmptyState.vue'
import type { DataTableColumn } from '@/components/DataTable.vue'
import type { Expense } from '@/types'

const expenseStore = useExpenseStore()
const household = useHouseholdStore()
const router = useRouter()

const categories = [
  { value: 'utilities', label: 'Režije' },
  { value: 'groceries', label: 'Namirnice' },
  { value: 'household', label: 'Kućanstvo' },
  { value: 'food', label: 'Hrana / izlasci' },
  { value: 'rent', label: 'Stanarina' },
  { value: 'transport', label: 'Prijevoz' },
  { value: 'other', label: 'Ostalo' },
]

const categoryLabels: Record<string, string> = Object.fromEntries(categories.map((c) => [c.value, c.label]))

// Filters
const search = ref('')
const selectedCategories = ref<string[]>([])
const paidBy = ref('')
const dateFrom = ref('')
const dateTo = ref('')

const memberOptions = computed(() => household.members.map((m) => ({ value: m.id, label: m.name })))

const columns: DataTableColumn[] = [
  { key: 'expense_date', label: 'Datum' },
  { key: 'title', label: 'Naziv' },
  { key: 'category', label: 'Kategorija' },
  { key: 'amount', label: 'Iznos', align: 'right' },
  { key: 'payer', label: 'Platio' },
]

const rows = computed(() =>
  expenseStore.expenses.map((e) => ({
    id: e.id,
    expense_date: new Date(e.expense_date).toLocaleDateString('hr-HR'),
    title: e.title,
    category: categoryLabels[e.category] ?? e.category,
    amount: `${Number(e.amount).toFixed(2)} €`,
    payer: e.payer?.name ?? '—',
  }))
)

const displayedTotal = computed(() =>
  expenseStore.expenses.reduce((sum, expense) => sum + Number(expense.amount), 0)
)

function applyFilters() {
  expenseStore.fetchExpenses({
    search: search.value || undefined,
    category: selectedCategories.value.length ? selectedCategories.value : undefined,
    paid_by_user_id: paidBy.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    page: 1,
  })
}

let searchTimeout: ReturnType<typeof setTimeout> | undefined
watch(search, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 400)
})

watch([selectedCategories, paidBy, dateFrom, dateTo], applyFilters, { deep: true })

function toggleCategory(value: string) {
  selectedCategories.value = selectedCategories.value.includes(value)
    ? selectedCategories.value.filter((category) => category !== value)
    : [...selectedCategories.value, value]
}

function resetFilters() {
  search.value = ''
  selectedCategories.value = []
  paidBy.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  applyFilters()
}

function goToPage(page: number) {
  expenseStore.fetchExpenses({ page })
}

// Create / edit modal
const formOpen = ref(false)
const editingExpense = ref<Expense | null>(null)
const formError = ref('')

function openCreate() {
  editingExpense.value = null
  formError.value = ''
  formOpen.value = true
}

function openEdit(expense: Expense) {
  editingExpense.value = expense
  formError.value = ''
  formOpen.value = true
}

async function onFormSubmit(formData: FormData) {
  formError.value = ''
  try {
    if (editingExpense.value) {
      await expenseStore.updateExpense(editingExpense.value.id, formData)
    } else {
      await expenseStore.createExpense(formData)
    }
    await expenseStore.fetchExpenses()
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
const expenseToDelete = ref<number | null>(null)

function askDelete(id: number) {
  expenseToDelete.value = id
  confirmDeleteOpen.value = true
}

async function onConfirmDelete() {
  if (expenseToDelete.value === null) return
  await expenseStore.deleteExpense(expenseToDelete.value)
  expenseToDelete.value = null
  await expenseStore.fetchExpenses()
}

function viewDetails(id: number) {
  router.push(`/expenses/${id}`)
}

onMounted(async () => {
  if (!household.currentHousehold) {
    await household.fetchHousehold()
  }
  await expenseStore.fetchExpenses()
})
</script>

<template>
  <div class="space-y-6">
    <div class="app-card">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-ink">Filtri</h2>
        <BaseButton variant="primary" @click="openCreate">+ Novi trošak</BaseButton>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <BaseInput v-model="search" label="Pretraži po nazivu" placeholder="npr. Struja" />
        <div class="sm:col-span-2 lg:col-span-2">
          <span class="form-label">Kategorija</span>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="option in categories"
              :key="option.value"
              type="button"
              class="category-filter"
              :class="{ 'category-filter-active': selectedCategories.includes(option.value) }"
              @click="toggleCategory(option.value)"
            >
              {{ option.label }}
            </button>
          </div>
        </div>
        <BaseSelect v-model="paidBy" label="Platio" :options="memberOptions" placeholder="Svi članovi" />
        <BaseInput v-model="dateFrom" type="date" label="Od datuma" />
        <BaseInput v-model="dateTo" type="date" label="Do datuma" />
      </div>
      <div class="mt-3">
        <button class="btn btn-ghost" @click="resetFilters">Poništi filtre</button>
      </div>
    </div>

    <div class="app-card">
      <div v-if="rows.length > 0" class="flex justify-end mb-3">
        <div class="expense-total-summary">
          <span>Zbroj prikazanih troškova</span>
          <strong>{{ displayedTotal.toFixed(2) }} €</strong>
        </div>
      </div>

      <EmptyState
        v-if="!expenseStore.loading && rows.length === 0"
        title="Nema troškova za odabrane filtre"
        description="Dodajte novi trošak ili promijenite filtre pretrage."
      />
      <DataTable v-else :columns="columns" :rows="rows" :loading="expenseStore.loading">
        <template #cell-title="{ row }">
          <button class="text-ink font-medium hover:text-accent" @click="viewDetails(row.id)">
            {{ row.title }}
          </button>
        </template>
        <template #actions="{ row }">
          <div class="flex justify-end gap-2">
            <button
              class="btn btn-ghost"
              @click="openEdit(expenseStore.expenses.find((e) => e.id === row.id)!)"
            >
              Uredi
            </button>
            <button class="btn btn-ghost text-accent" @click="askDelete(row.id)">Obriši</button>
          </div>
        </template>
      </DataTable>

      <div v-if="expenseStore.lastPage > 1" class="flex items-center justify-between mt-4 text-sm">
        <span class="text-muted">
          Stranica {{ expenseStore.currentPage }} od {{ expenseStore.lastPage }} ({{ expenseStore.total }} ukupno)
        </span>
        <div class="flex gap-2">
          <button
            class="btn btn-secondary"
            :disabled="expenseStore.currentPage <= 1"
            @click="goToPage(expenseStore.currentPage - 1)"
          >
            Prethodna
          </button>
          <button
            class="btn btn-secondary"
            :disabled="expenseStore.currentPage >= expenseStore.lastPage"
            @click="goToPage(expenseStore.currentPage + 1)"
          >
            Sljedeća
          </button>
        </div>
      </div>
    </div>

    <BaseModal v-model="formOpen" :title="editingExpense ? 'Uredi trošak' : 'Novi trošak'">
      <p v-if="formError" class="text-sm text-accent mb-3">{{ formError }}</p>
      <ExpenseForm
        :expense="editingExpense"
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
