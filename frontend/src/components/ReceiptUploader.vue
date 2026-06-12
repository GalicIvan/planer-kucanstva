<script setup lang="ts">
import { ref } from 'vue'
import BaseSelect from './BaseSelect.vue'
import BaseButton from './BaseButton.vue'
import type { Expense } from '@/types'

const props = defineProps<{ expenses: Expense[] }>()
const emit = defineEmits<{ upload: [expenseId: number, file: File] }>()

const expenseId = ref<string>('')
const file = ref<File | null>(null)

const expenseOptions = () =>
  props.expenses.map((e) => ({ value: e.id, label: `${e.title} (${Number(e.amount).toFixed(2)} €)` }))

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  file.value = target.files?.[0] ?? null
}

function submit() {
  if (!expenseId.value || !file.value) return
  emit('upload', Number(expenseId.value), file.value)
  file.value = null
}
</script>

<template>
  <form class="space-y-4 app-card" @submit.prevent="submit">
    <h3 class="font-medium text-ink">Upload računa</h3>
    <BaseSelect v-model="expenseId" label="Trošak" :options="expenseOptions()" placeholder="Odaberi trošak" />
    <div>
      <label class="form-label">Datoteka (slika ili PDF)</label>
      <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="form-input" @change="onFileChange" />
    </div>
    <BaseButton variant="primary" type="submit" :disabled="!expenseId || !file">Učitaj račun</BaseButton>
  </form>
</template>
