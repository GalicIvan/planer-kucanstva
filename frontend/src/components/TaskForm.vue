<script setup lang="ts">
import { ref, watch } from 'vue'
import BaseInput from './BaseInput.vue'
import BaseSelect from './BaseSelect.vue'
import BaseButton from './BaseButton.vue'
import type { Task, User } from '@/types'
import type { TaskPayload } from '@/services/task.service'

const props = defineProps<{
  task?: Task | null
  members: User[]
}>()

const emit = defineEmits<{ submit: [payload: TaskPayload]; cancel: [] }>()

const title = ref('')
const description = ref('')
const assignedTo = ref<string>('')
const dueDate = ref('')
const status = ref<'pending' | 'done'>('pending')

function reset() {
  title.value = props.task?.title ?? ''
  description.value = props.task?.description ?? ''
  assignedTo.value = props.task?.assigned_to_user_id ? String(props.task.assigned_to_user_id) : ''
  dueDate.value = props.task?.due_date?.slice(0, 10) ?? ''
  status.value = props.task?.status ?? 'pending'
}

watch(() => props.task, reset, { immediate: true })

const memberOptions = () => props.members.map((m) => ({ value: m.id, label: m.name }))

function submit() {
  emit('submit', {
    title: title.value,
    description: description.value || null,
    assigned_to_user_id: assignedTo.value ? Number(assignedTo.value) : null,
    due_date: dueDate.value || null,
    status: status.value,
  })
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="submit">
    <BaseInput v-model="title" label="Naziv zadatka" required placeholder="npr. Iznijeti smeće" />

    <div>
      <label class="form-label">Opis (opcionalno)</label>
      <textarea v-model="description" class="form-textarea" rows="2"></textarea>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <BaseSelect v-model="assignedTo" label="Dodijeli korisniku" :options="memberOptions()" placeholder="Nedodijeljeno" />
      <BaseInput v-model="dueDate" type="date" label="Rok" />
    </div>

    <BaseSelect
      v-model="status"
      label="Status"
      :options="[{ value: 'pending', label: 'Na čekanju' }, { value: 'done', label: 'Završeno' }]"
    />

    <div class="flex justify-end gap-2 pt-2">
      <BaseButton variant="secondary" type="button" @click="emit('cancel')">Odustani</BaseButton>
      <BaseButton variant="primary" type="submit">{{ task ? 'Spremi izmjene' : 'Dodaj zadatak' }}</BaseButton>
    </div>
  </form>
</template>
