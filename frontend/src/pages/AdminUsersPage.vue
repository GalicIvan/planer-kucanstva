<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { userService } from '@/services/user.service'
import { useAuthStore } from '@/stores/auth'
import type { User, UserRole } from '@/types'
import BaseInput from '@/components/BaseInput.vue'
import BaseSelect from '@/components/BaseSelect.vue'
import BaseBadge from '@/components/BaseBadge.vue'
import DataTable from '@/components/DataTable.vue'
import EmptyState from '@/components/EmptyState.vue'
import type { DataTableColumn } from '@/components/DataTable.vue'

const auth = useAuthStore()

const users = ref<User[]>([])
const loading = ref(true)
const error = ref('')
const actionError = ref('')

const search = ref('')
const roleFilter = ref('')

const isSuperAdmin = computed(() => auth.hasRole('super_admin'))

const roleOptions = [
  { value: 'user', label: 'Korisnik' },
  { value: 'admin', label: 'Administrator' },
  { value: 'super_admin', label: 'Super admin' },
]

const columns: DataTableColumn[] = [
  { key: 'name', label: 'Ime' },
  { key: 'email', label: 'E-mail' },
  { key: 'role', label: 'Uloga' },
  { key: 'is_active', label: 'Status' },
]

const rows = computed(() =>
  users.value.map((u) => ({
    id: u.id,
    name: u.name,
    email: u.email,
    role: u.role,
    is_active: u.is_active,
  }))
)

function roleLabel(role: UserRole) {
  return roleOptions.find((r) => r.value === role)?.label ?? role
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await userService.list({
      search: search.value || undefined,
      role: roleFilter.value || undefined,
    })
    users.value = data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Greška prilikom dohvaćanja korisnika.'
  } finally {
    loading.value = false
  }
}

let searchTimeout: ReturnType<typeof setTimeout> | undefined
watch(search, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(load, 400)
})

watch(roleFilter, load)

async function onChangeRole(userId: number, role: string) {
  actionError.value = ''
  try {
    const { data } = await userService.changeRole(userId, role as UserRole)
    const idx = users.value.findIndex((u) => u.id === userId)
    if (idx !== -1) users.value[idx] = data
  } catch (err: any) {
    actionError.value = err.response?.data?.message || 'Greška prilikom promjene uloge.'
  }
}

async function onToggleActive(userId: number) {
  actionError.value = ''
  try {
    const { data } = await userService.toggleActive(userId)
    const idx = users.value.findIndex((u) => u.id === userId)
    if (idx !== -1) users.value[idx] = data
  } catch (err: any) {
    actionError.value = err.response?.data?.message || 'Greška prilikom promjene statusa.'
  }
}

onMounted(load)
</script>

<template>
  <div class="space-y-6">
    <div class="app-card">
      <h2 class="font-semibold text-ink mb-3">Pretraga korisnika</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <BaseInput v-model="search" label="Pretraži po imenu ili e-mailu" placeholder="npr. Ana" />
        <BaseSelect v-model="roleFilter" label="Uloga" :options="roleOptions" placeholder="Sve uloge" />
      </div>
    </div>

    <div class="app-card">
      <p v-if="error" class="text-sm text-accent mb-3">{{ error }}</p>
      <p v-if="actionError" class="text-sm text-accent mb-3">{{ actionError }}</p>

      <EmptyState v-if="!loading && rows.length === 0" title="Nema korisnika za odabrane filtre" />
      <DataTable v-else :columns="columns" :rows="rows" :loading="loading">
        <template #cell-role="{ row, value }">
          <BaseSelect
            v-if="isSuperAdmin"
            :model-value="value"
            :options="roleOptions"
            @update:model-value="(v) => onChangeRole(row.id, v)"
          />
          <BaseBadge v-else variant="default">{{ roleLabel(value) }}</BaseBadge>
        </template>
        <template #cell-is_active="{ value }">
          <BaseBadge :variant="value ? 'success' : 'pending'">
            {{ value ? 'Aktivan' : 'Deaktiviran' }}
          </BaseBadge>
        </template>
        <template v-if="isSuperAdmin" #actions="{ row }">
          <button class="btn btn-ghost" @click="onToggleActive(row.id)">
            {{ row.is_active ? 'Deaktiviraj' : 'Aktiviraj' }}
          </button>
        </template>
      </DataTable>
    </div>
  </div>
</template>
