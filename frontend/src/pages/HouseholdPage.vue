<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useHouseholdStore } from '@/stores/household'
import { useAuthStore } from '@/stores/auth'
import { householdService } from '@/services/household.service'
import BaseInput from '@/components/BaseInput.vue'
import BaseSelect from '@/components/BaseSelect.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseBadge from '@/components/BaseBadge.vue'
import DataTable from '@/components/DataTable.vue'
import EmptyState from '@/components/EmptyState.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import type { DataTableColumn } from '@/components/DataTable.vue'

const household = useHouseholdStore()
const auth = useAuthStore()

const loading = ref(true)
const notFound = ref(false)

// Create household form
const newName = ref('')
const newDescription = ref('')
const createError = ref('')
const creating = ref(false)

// Edit household
const editing = ref(false)
const editName = ref('')
const editDescription = ref('')
const editError = ref('')
const saving = ref(false)

// Add member form
const memberEmail = ref('')
const memberRole = ref<'member' | 'admin'>('member')
const addError = ref('')
const adding = ref(false)

// Remove member
const confirmRemoveOpen = ref(false)
const memberToRemove = ref<number | null>(null)

const canManage = computed(() => auth.hasRole('admin', 'super_admin'))

const columns: DataTableColumn[] = [
  { key: 'name', label: 'Ime' },
  { key: 'email', label: 'E-mail' },
  { key: 'member_role', label: 'Uloga u kućanstvu' },
  { key: 'role', label: 'Sistemska uloga' },
]

const rows = computed(() =>
  household.members.map((m) => ({
    id: m.id,
    name: m.name,
    email: m.email,
    member_role: m.pivot?.member_role ?? 'member',
    role: m.role,
  }))
)

async function load() {
  loading.value = true
  notFound.value = false
  await household.fetchHousehold()
  if (!household.currentHousehold) {
    notFound.value = true
  } else {
    editName.value = household.currentHousehold.name
    editDescription.value = household.currentHousehold.description ?? ''
  }
  loading.value = false
}

onMounted(load)

async function onCreate() {
  createError.value = ''
  creating.value = true
  try {
    await household.createHousehold(newName.value, newDescription.value || undefined)
    notFound.value = false
    editName.value = household.currentHousehold?.name ?? ''
    editDescription.value = household.currentHousehold?.description ?? ''
  } catch (err: any) {
    createError.value = err.response?.data?.message || 'Greška prilikom stvaranja kućanstva.'
  } finally {
    creating.value = false
  }
}

async function onSaveEdit() {
  if (!household.currentHousehold) return
  editError.value = ''
  saving.value = true
  try {
    const { data } = await householdService.update(household.currentHousehold.id, {
      name: editName.value,
      description: editDescription.value || undefined,
    })
    household.currentHousehold = data
    editing.value = false
  } catch (err: any) {
    editError.value = err.response?.data?.message || 'Greška prilikom spremanja.'
  } finally {
    saving.value = false
  }
}

async function onAddMember() {
  addError.value = ''
  adding.value = true
  try {
    await household.addMember(memberEmail.value, memberRole.value)
    memberEmail.value = ''
    memberRole.value = 'member'
  } catch (err: any) {
    addError.value = err.response?.data?.message || 'Greška prilikom dodavanja člana.'
  } finally {
    adding.value = false
  }
}

function askRemove(userId: number) {
  memberToRemove.value = userId
  confirmRemoveOpen.value = true
}

async function onConfirmRemove() {
  if (memberToRemove.value === null) return
  await household.removeMember(memberToRemove.value)
  memberToRemove.value = null
}
</script>

<template>
  <div class="space-y-6">
    <div v-if="loading" class="app-card text-center text-muted py-10">Učitavanje...</div>

    <template v-else-if="notFound">
      <div class="app-card max-w-lg">
        <h2 class="font-semibold text-ink mb-1">Još nemate kućanstvo</h2>
        <p class="text-sm text-muted mb-4">
          Stvorite kućanstvo da biste mogli pratiti zajedničke troškove, zadatke i popis za kupnju.
        </p>
        <form class="space-y-4" @submit.prevent="onCreate">
          <BaseInput v-model="newName" label="Naziv kućanstva" required placeholder="npr. Stan u Zagrebu" />
          <div>
            <label class="form-label">Opis (opcionalno)</label>
            <textarea v-model="newDescription" class="form-textarea" rows="2"></textarea>
          </div>
          <p v-if="createError" class="text-sm text-accent">{{ createError }}</p>
          <BaseButton type="submit" variant="primary" :disabled="creating">
            {{ creating ? 'Stvaranje...' : 'Stvori kućanstvo' }}
          </BaseButton>
        </form>
      </div>
    </template>

    <template v-else-if="household.currentHousehold">
      <div class="app-card">
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1">
            <template v-if="!editing">
              <h2 class="font-semibold text-ink text-lg">{{ household.currentHousehold.name }}</h2>
              <p class="text-sm text-muted mt-1">
                {{ household.currentHousehold.description || 'Nema opisa.' }}
              </p>
            </template>
            <form v-else class="space-y-3 max-w-md" @submit.prevent="onSaveEdit">
              <BaseInput v-model="editName" label="Naziv kućanstva" required />
              <div>
                <label class="form-label">Opis</label>
                <textarea v-model="editDescription" class="form-textarea" rows="2"></textarea>
              </div>
              <p v-if="editError" class="text-sm text-accent">{{ editError }}</p>
              <div class="flex gap-2">
                <BaseButton type="submit" variant="primary" :disabled="saving">
                  {{ saving ? 'Spremanje...' : 'Spremi' }}
                </BaseButton>
                <BaseButton type="button" variant="secondary" @click="editing = false">Odustani</BaseButton>
              </div>
            </form>
          </div>
          <BaseButton v-if="canManage && !editing" variant="secondary" @click="editing = true">
            Uredi
          </BaseButton>
        </div>
      </div>

      <div class="app-card">
        <h3 class="font-semibold text-ink mb-3">Članovi kućanstva</h3>
        <EmptyState v-if="rows.length === 0" title="Nema članova" />
        <DataTable v-else :columns="columns" :rows="rows">
          <template #cell-member_role="{ value }">
            <BaseBadge :variant="value === 'admin' ? 'success' : 'default'">
              {{ value === 'admin' ? 'Admin kućanstva' : 'Član' }}
            </BaseBadge>
          </template>
          <template #cell-role="{ value }">
            <span class="text-muted">
              {{ value === 'super_admin' ? 'Super admin' : value === 'admin' ? 'Administrator' : 'Korisnik' }}
            </span>
          </template>
          <template v-if="canManage" #actions="{ row }">
            <button class="btn btn-ghost text-accent" @click="askRemove(row.id)">Ukloni</button>
          </template>
        </DataTable>
      </div>

      <div v-if="canManage" class="app-card max-w-lg">
        <h3 class="font-semibold text-ink mb-3">Dodaj člana kućanstva</h3>
        <form class="space-y-4" @submit.prevent="onAddMember">
          <BaseInput v-model="memberEmail" type="email" label="E-mail postojećeg korisnika" required placeholder="ime@primjer.com" />
          <BaseSelect
            v-model="memberRole"
            label="Uloga u kućanstvu"
            :options="[{ value: 'member', label: 'Član' }, { value: 'admin', label: 'Admin kućanstva' }]"
          />
          <p v-if="addError" class="text-sm text-accent">{{ addError }}</p>
          <BaseButton type="submit" variant="primary" :disabled="adding">
            {{ adding ? 'Dodavanje...' : 'Dodaj člana' }}
          </BaseButton>
        </form>
      </div>
    </template>

    <ConfirmDialog
      v-model="confirmRemoveOpen"
      title="Ukloni člana"
      message="Jeste li sigurni da želite ukloniti ovog člana iz kućanstva?"
      confirm-label="Ukloni"
      @confirm="onConfirmRemove"
    />
  </div>
</template>
