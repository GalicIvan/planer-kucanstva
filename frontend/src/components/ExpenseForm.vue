<script setup lang="ts">
import { ref, watch } from 'vue'
import BaseInput from './BaseInput.vue'
import BaseSelect from './BaseSelect.vue'
import BaseButton from './BaseButton.vue'
import type { Expense, User } from '@/types'

const props = defineProps<{
  expense?: Expense | null
  members: User[]
}>()

const emit = defineEmits<{ submit: [formData: FormData]; cancel: [] }>()

const categories = [
  { value: 'utilities', label: 'Režije' },
  { value: 'groceries', label: 'Namirnice' },
  { value: 'household', label: 'Kućanstvo' },
  { value: 'food', label: 'Hrana / izlasci' },
  { value: 'rent', label: 'Stanarina' },
  { value: 'transport', label: 'Prijevoz' },
  { value: 'other', label: 'Ostalo' },
]

const title = ref('')
const description = ref('')
const amount = ref<string>('')
const category = ref('other')
const expenseDate = ref('')
const splitWith = ref<number[]>([])
const file = ref<File | null>(null)

function reset() {
  title.value = props.expense?.title ?? ''
  description.value = props.expense?.description ?? ''
  amount.value = props.expense ? String(props.expense.amount) : ''
  category.value = props.expense?.category ?? 'other'
  expenseDate.value = props.expense?.expense_date?.slice(0, 10) ?? new Date().toISOString().slice(0, 10)
  splitWith.value = props.expense?.shares?.map((s) => s.user_id) ?? props.members.map((m) => m.id)
}

watch(() => props.expense, reset, { immediate: true })

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  file.value = target.files?.[0] ?? null
}

function toggleMember(id: number) {
  if (splitWith.value.includes(id)) {
    splitWith.value = splitWith.value.filter((m) => m !== id)
  } else {
    splitWith.value = [...splitWith.value, id]
  }
}

function submit() {
  const formData = new FormData()
  formData.append('title', title.value)
  formData.append('description', description.value || '')
  formData.append('amount', amount.value)
  formData.append('category', category.value)
  formData.append('expense_date', expenseDate.value)
  splitWith.value.forEach((id) => formData.append('split_with[]', String(id)))
  if (file.value) formData.append('receipt', file.value)
  emit('submit', formData)
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="submit">
    <BaseInput v-model="title" label="Naziv troška" required placeholder="npr. Struja - lipanj" />

    <div class="grid grid-cols-2 gap-3">
      <BaseInput v-model="amount" type="number" label="Iznos (EUR)" required placeholder="0.00" />
      <BaseInput v-model="expenseDate" type="date" label="Datum troška" required />
    </div>

    <BaseSelect v-model="category" label="Kategorija" :options="categories" />

    <div>
      <label class="form-label">Opis (opcionalno)</label>
      <textarea v-model="description" class="form-textarea" rows="2"></textarea>
    </div>

    <div>
      <label class="form-label">Podijeli trošak s članovima kućanstva</label>
      <div class="flex flex-wrap gap-2 mt-1">
        <label
          v-for="member in members"
          :key="member.id"
          class="flex items-center gap-1.5 border border-secondary rounded px-2 py-1 text-sm cursor-pointer"
          :class="splitWith.includes(member.id) ? 'bg-surface' : 'bg-white'"
        >
          <input type="checkbox" :checked="splitWith.includes(member.id)" @change="toggleMember(member.id)" />
          {{ member.name }}
        </label>
      </div>
    </div>

    <div>
      <label class="form-label">Račun (slika ili PDF, opcionalno)</label>
      <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="form-input" @change="onFileChange" />
    </div>

    <div class="flex justify-end gap-2 pt-2">
      <BaseButton variant="secondary" type="button" @click="emit('cancel')">Odustani</BaseButton>
      <BaseButton variant="primary" type="submit">{{ expense ? 'Spremi izmjene' : 'Dodaj trošak' }}</BaseButton>
    </div>
  </form>
</template>
