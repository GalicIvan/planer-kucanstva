<script setup lang="ts">
defineProps<{
  modelValue: boolean
  title?: string
}>()

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()

function close() {
  emit('update:modelValue', false)
}
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-ink/40" @click="close" />
      <div class="relative bg-white rounded-lg border border-secondary w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-ink">{{ title }}</h3>
          <button class="text-muted hover:text-ink text-xl leading-none" @click="close">&times;</button>
        </div>
        <slot />
      </div>
    </div>
  </Teleport>
</template>
