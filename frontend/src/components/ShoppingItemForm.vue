<script setup lang="ts">
import { ref, watch } from 'vue'
import BaseInput from './BaseInput.vue'
import BaseButton from './BaseButton.vue'
import type { ShoppingItem } from '@/types'
import type { ShoppingItemPayload } from '@/services/shopping.service'

const props = defineProps<{ item?: ShoppingItem | null }>()
const emit = defineEmits<{ submit: [payload: ShoppingItemPayload]; cancel: [] }>()

const name = ref('')
const quantity = ref<string>('1')

function reset() {
  name.value = props.item?.name ?? ''
  quantity.value = props.item ? String(props.item.quantity) : '1'
}

watch(() => props.item, reset, { immediate: true })

function submit() {
  emit('submit', { name: name.value, quantity: Number(quantity.value) || 1 })
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="submit">
    <BaseInput v-model="name" label="Naziv artikla" required placeholder="npr. Mlijeko" />
    <BaseInput v-model="quantity" type="number" label="Količina" required placeholder="1" />
    <div class="flex justify-end gap-2 pt-2">
      <BaseButton variant="secondary" type="button" @click="emit('cancel')">Odustani</BaseButton>
      <BaseButton variant="primary" type="submit">{{ item ? 'Spremi izmjene' : 'Dodaj artikl' }}</BaseButton>
    </div>
  </form>
</template>
