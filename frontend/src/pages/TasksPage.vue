<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useTaskStore } from '@/stores/task'
import { useHouseholdStore } from '@/stores/household'
import BaseSelect from '@/components/BaseSelect.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseBadge from '@/components/BaseBadge.vue'
import BaseModal from '@/components/BaseModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import DataTable from '@/components/DataTable.vue'
import TaskForm from '@/components/TaskForm.vue'
import EmptyState from '@/components/EmptyState.vue'
import type { DataTableColumn } from '@/components/DataTable.vue'
import type { Task, TaskStatus } from '@/types'
import type { TaskPayload } from '@/services/task.service'

const taskStore = useTaskStore()
const household = useHouseholdStore()

const statusFilter = ref<TaskStatus | ''>('')
const assigneeFilter = ref('')

const memberOptions = computed(() => household.members.map((m) => ({ value: m.id, label: m.name })))

const columns: DataTableColumn[] = [
  { key: 'title', label: 'Naziv' },
  { key: 'assignee', label: 'Dodijeljeno' },
  { key: 'due_date', label: 'Rok' },
  { key: 'status', label: 'Status' },
]

const rows = computed(() =>
  taskStore.tasks.map((t) => ({
    id: t.id,
    title: t.title,
    assignee: t.assignee?.name ?? 'Nedodijeljeno',
    due_date: t.due_date ? new Date(t.due_date).toLocaleDateString('hr-HR') : '—',
    status: t.status,
  }))
)

function applyFilters() {
  taskStore.fetchTasks({
    status: statusFilter.value || undefined,
    assigned_to_user_id: assigneeFilter.value || undefined,
  })
}

watch([statusFilter, assigneeFilter], applyFilters)

function resetFilters() {
  statusFilter.value = ''
  assigneeFilter.value = ''
  applyFilters()
}

// Create / edit modal
const formOpen = ref(false)
const editingTask = ref<Task | null>(null)
const formError = ref('')

function openCreate() {
  editingTask.value = null
  formError.value = ''
  formOpen.value = true
}

function openEdit(task: Task) {
  editingTask.value = task
  formError.value = ''
  formOpen.value = true
}

async function onFormSubmit(payload: TaskPayload) {
  formError.value = ''
  try {
    if (editingTask.value) {
      await taskStore.updateTask(editingTask.value.id, payload)
    } else {
      await taskStore.createTask(payload)
    }
    await taskStore.fetchTasks()
    formOpen.value = false
  } catch (err: any) {
    const errors = err.response?.data?.errors
    formError.value = errors
      ? Object.values(errors).flat().join(' ')
      : err.response?.data?.message || 'Greška prilikom spremanja zadatka.'
  }
}

async function toggleStatus(task: { id: number; status: TaskStatus }) {
  const next: TaskStatus = task.status === 'done' ? 'pending' : 'done'
  await taskStore.markDone(task.id, next)
  await taskStore.fetchTasks()
}

// Delete
const confirmDeleteOpen = ref(false)
const taskToDelete = ref<number | null>(null)

function askDelete(id: number) {
  taskToDelete.value = id
  confirmDeleteOpen.value = true
}

async function onConfirmDelete() {
  if (taskToDelete.value === null) return
  await taskStore.deleteTask(taskToDelete.value)
  taskToDelete.value = null
  await taskStore.fetchTasks()
}

onMounted(async () => {
  if (!household.currentHousehold) {
    await household.fetchHousehold()
  }
  await taskStore.fetchTasks()
})
</script>

<template>
  <div class="space-y-6">
    <div class="app-card">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-ink">Filtri</h2>
        <BaseButton variant="primary" @click="openCreate">+ Novi zadatak</BaseButton>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <BaseSelect
          v-model="statusFilter"
          label="Status"
          :options="[{ value: 'pending', label: 'Na čekanju' }, { value: 'done', label: 'Završeno' }]"
          placeholder="Svi statusi"
        />
        <BaseSelect v-model="assigneeFilter" label="Dodijeljeno" :options="memberOptions" placeholder="Svi članovi" />
      </div>
      <div class="mt-3">
        <button class="btn btn-ghost" @click="resetFilters">Poništi filtre</button>
      </div>
    </div>

    <div class="app-card">
      <EmptyState
        v-if="!taskStore.loading && rows.length === 0"
        title="Nema zadataka"
        description="Dodajte novi zadatak za kućanstvo."
      />
      <DataTable v-else :columns="columns" :rows="rows" :loading="taskStore.loading">
        <template #cell-title="{ row }">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              :checked="row.status === 'done'"
              @change="toggleStatus(row as { id: number; status: TaskStatus })"
            />
            <span :class="row.status === 'done' ? 'line-through text-muted' : 'text-ink'">{{ row.title }}</span>
          </label>
        </template>
        <template #cell-status="{ value }">
          <BaseBadge :variant="value === 'done' ? 'success' : 'pending'">
            {{ value === 'done' ? 'Završeno' : 'Na čekanju' }}
          </BaseBadge>
        </template>
        <template #actions="{ row }">
          <div class="flex justify-end gap-2">
            <button class="btn btn-ghost" @click="openEdit(taskStore.tasks.find((t) => t.id === row.id)!)">
              Uredi
            </button>
            <button class="btn btn-ghost text-accent" @click="askDelete(row.id)">Obriši</button>
          </div>
        </template>
      </DataTable>
    </div>

    <BaseModal v-model="formOpen" :title="editingTask ? 'Uredi zadatak' : 'Novi zadatak'">
      <p v-if="formError" class="text-sm text-accent mb-3">{{ formError }}</p>
      <TaskForm :task="editingTask" :members="household.members" @submit="onFormSubmit" @cancel="formOpen = false" />
    </BaseModal>

    <ConfirmDialog
      v-model="confirmDeleteOpen"
      title="Obriši zadatak"
      message="Jeste li sigurni da želite obrisati ovaj zadatak?"
      confirm-label="Obriši"
      @confirm="onConfirmDelete"
    />
  </div>
</template>
