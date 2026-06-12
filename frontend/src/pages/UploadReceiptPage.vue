<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { receiptService } from '@/services/receipt.service'
import { expenseService } from '@/services/expense.service'
import type { Expense, Receipt } from '@/types'
import ReceiptUploader from '@/components/ReceiptUploader.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'

const expenses = ref<Expense[]>([])
const receipts = ref<Receipt[]>([])
const loading = ref(true)
const error = ref('')
const uploadError = ref('')
const uploading = ref(false)

const storageBaseUrl = computed(() => {
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  return apiUrl.replace(/\/api\/?$/, '')
})

function fileUrl(path: string) {
  return `${storageBaseUrl.value}/storage/${path}`
}

function formatDate(value?: string) {
  if (!value) return ''
  return new Date(value).toLocaleDateString('hr-HR')
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const [expensesRes, receiptsRes] = await Promise.all([
      expenseService.list({ per_page: 100 }),
      receiptService.list(),
    ])
    expenses.value = expensesRes.data.data
    receipts.value = receiptsRes.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Greška prilikom dohvaćanja podataka.'
  } finally {
    loading.value = false
  }
}

async function onUpload(expenseId: number, file: File) {
  uploadError.value = ''
  uploading.value = true
  try {
    const { data } = await receiptService.upload(expenseId, file)
    receipts.value.unshift(data)
  } catch (err: any) {
    const errors = err.response?.data?.errors
    uploadError.value = errors
      ? Object.values(errors).flat().join(' ')
      : err.response?.data?.message || 'Greška prilikom učitavanja računa.'
  } finally {
    uploading.value = false
  }
}

// Delete
const confirmDeleteOpen = ref(false)
const receiptToDelete = ref<number | null>(null)

function askDelete(id: number) {
  receiptToDelete.value = id
  confirmDeleteOpen.value = true
}

async function onConfirmDelete() {
  if (receiptToDelete.value === null) return
  await receiptService.remove(receiptToDelete.value)
  receipts.value = receipts.value.filter((r) => r.id !== receiptToDelete.value)
  receiptToDelete.value = null
}

onMounted(load)
</script>

<template>
  <div class="space-y-6">
    <div v-if="error" class="app-card text-accent text-sm">{{ error }}</div>

    <template v-else>
      <div>
        <p v-if="uploadError" class="text-sm text-accent mb-3">{{ uploadError }}</p>
        <ReceiptUploader :expenses="expenses" @upload="onUpload" />
        <p v-if="uploading" class="text-sm text-muted mt-2">Učitavanje u tijeku...</p>
      </div>

      <div class="app-card">
        <h3 class="font-semibold text-ink mb-3">Učitani računi</h3>
        <div v-if="loading" class="text-center text-muted py-6">Učitavanje...</div>
        <EmptyState
          v-else-if="receipts.length === 0"
          title="Nema učitanih računa"
          description="Odaberite trošak i učitajte sliku ili PDF računa."
        />
        <ul v-else class="divide-y divide-surface">
          <li v-for="receipt in receipts" :key="receipt.id" class="py-2.5 flex items-center justify-between gap-3">
            <div>
              <p class="text-sm font-medium text-ink">{{ receipt.original_name }}</p>
              <p class="text-xs text-muted">
                {{ receipt.expense?.title }} · učitao {{ receipt.user?.name }}
                <span v-if="receipt.created_at"> · {{ formatDate(receipt.created_at) }}</span>
              </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
              <a :href="fileUrl(receipt.file_path)" target="_blank" class="text-accent font-medium text-sm">Otvori</a>
              <button class="btn btn-ghost text-accent" @click="askDelete(receipt.id)">Obriši</button>
            </div>
          </li>
        </ul>
      </div>
    </template>

    <ConfirmDialog
      v-model="confirmDeleteOpen"
      title="Obriši račun"
      message="Jeste li sigurni da želite obrisati ovaj račun?"
      confirm-label="Obriši"
      @confirm="onConfirmDelete"
    />
  </div>
</template>
