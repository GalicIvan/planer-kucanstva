<script setup lang="ts">
import BaseButton from './BaseButton.vue'
import BaseModal from './BaseModal.vue'

withDefaults(
  defineProps<{
    modelValue: boolean
    title?: string
    message: string
    confirmLabel?: string
  }>(),
  {
    title: 'Potvrda',
    confirmLabel: 'Potvrdi',
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  confirm: []
}>()

function cancel() {
  emit('update:modelValue', false)
}

function confirm() {
  emit('confirm')
  emit('update:modelValue', false)
}
</script>

<template>
  <BaseModal :model-value="modelValue" :title="title" @update:model-value="emit('update:modelValue', $event)">
    <p class="text-sm text-muted mb-6">{{ message }}</p>
    <div class="flex justify-end gap-2">
      <BaseButton variant="secondary" @click="cancel">Odustani</BaseButton>
      <BaseButton variant="danger" @click="confirm">{{ confirmLabel }}</BaseButton>
    </div>
  </BaseModal>
</template>
