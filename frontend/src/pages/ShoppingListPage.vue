<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useShoppingStore } from '@/stores/shopping'
import BaseButton from '@/components/BaseButton.vue'
import BaseModal from '@/components/BaseModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ShoppingItemForm from '@/components/ShoppingItemForm.vue'
import EmptyState from '@/components/EmptyState.vue'
import type { ShoppingItem } from '@/types'
import type { ShoppingItemPayload } from '@/services/shopping.service'

const shopping = useShoppingStore()

const pending = computed(() => shopping.items.filter((i) => !i.is_purchased))
const purchased = computed(() => shopping.items.filter((i) => i.is_purchased))

// Create / edit modal
const formOpen = ref(false)
const editingItem = ref<ShoppingItem | null>(null)
const formError = ref('')

function openCreate() {
  editingItem.value = null
  formError.value = ''
  formOpen.value = true
}

function openEdit(item: ShoppingItem) {
  editingItem.value = item
  formError.value = ''
  formOpen.value = true
}

async function onFormSubmit(payload: ShoppingItemPayload) {
  formError.value = ''
  try {
    if (editingItem.value) {
      await shopping.updateItem(editingItem.value.id, payload)
    } else {
      await shopping.createItem(payload)
    }
    formOpen.value = false
  } catch (err: any) {
    const errors = err.response?.data?.errors
    formError.value = errors
      ? Object.values(errors).flat().join(' ')
      : err.response?.data?.message || 'Greška prilikom spremanja artikla.'
  }
}

async function togglePurchased(item: ShoppingItem) {
  await shopping.markPurchased(item.id, !item.is_purchased)
}

// Delete
const confirmDeleteOpen = ref(false)
const itemToDelete = ref<number | null>(null)

function askDelete(id: number) {
  itemToDelete.value = id
  confirmDeleteOpen.value = true
}

async function onConfirmDelete() {
  if (itemToDelete.value === null) return
  await shopping.deleteItem(itemToDelete.value)
  itemToDelete.value = null
}

onMounted(() => shopping.fetchItems())
</script>

<template>
  <div class="space-y-6">
    <div class="app-card">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="font-semibold text-ink">Popis za kupnju</h2>
          <p class="text-sm text-muted mt-1">Zajednički popis namirnica i potrepština za kućanstvo.</p>
        </div>
        <BaseButton variant="primary" @click="openCreate">+ Novi artikl</BaseButton>
      </div>
    </div>

    <div class="app-card">
      <h3 class="font-semibold text-ink mb-3">Za kupiti</h3>
      <EmptyState v-if="!shopping.loading && pending.length === 0" title="Sve je kupljeno" description="Nema artikala koje treba kupiti." />
      <ul v-else class="divide-y divide-surface">
        <li v-for="item in pending" :key="item.id" class="py-2.5 flex items-center justify-between gap-3">
          <label class="flex items-center gap-3 cursor-pointer flex-1">
            <input type="checkbox" :checked="item.is_purchased" @change="togglePurchased(item)" />
            <span class="text-sm text-ink">{{ item.name }}</span>
            <span class="text-xs text-muted">× {{ item.quantity }}</span>
          </label>
          <div class="flex gap-2 shrink-0">
            <button class="btn btn-ghost" @click="openEdit(item)">Uredi</button>
            <button class="btn btn-ghost text-accent" @click="askDelete(item.id)">Obriši</button>
          </div>
        </li>
      </ul>
    </div>

    <div v-if="purchased.length > 0" class="app-card">
      <h3 class="font-semibold text-ink mb-3">Kupljeno</h3>
      <ul class="divide-y divide-surface">
        <li v-for="item in purchased" :key="item.id" class="py-2.5 flex items-center justify-between gap-3">
          <label class="flex items-center gap-3 cursor-pointer flex-1">
            <input type="checkbox" :checked="item.is_purchased" @change="togglePurchased(item)" />
            <span class="text-sm text-muted line-through">{{ item.name }}</span>
            <span class="text-xs text-muted">× {{ item.quantity }}</span>
          </label>
          <div class="flex gap-2 shrink-0">
            <button class="btn btn-ghost" @click="openEdit(item)">Uredi</button>
            <button class="btn btn-ghost text-accent" @click="askDelete(item.id)">Obriši</button>
          </div>
        </li>
      </ul>
    </div>

    <BaseModal v-model="formOpen" :title="editingItem ? 'Uredi artikl' : 'Novi artikl'">
      <p v-if="formError" class="text-sm text-accent mb-3">{{ formError }}</p>
      <ShoppingItemForm :item="editingItem" @submit="onFormSubmit" @cancel="formOpen = false" />
    </BaseModal>

    <ConfirmDialog
      v-model="confirmDeleteOpen"
      title="Obriši artikl"
      message="Jeste li sigurni da želite obrisati ovaj artikl s popisa?"
      confirm-label="Obriši"
      @confirm="onConfirmDelete"
    />
  </div>
</template>
