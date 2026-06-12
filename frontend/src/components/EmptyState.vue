<script setup lang="ts">
defineProps<{
  title: string
  description?: string
}>()
</script>

<template>
  <div class="flex flex-col items-center justify-center text-center py-12 px-4">
    <div class="w-12 h-12 rounded-full bg-surface flex items-center justify-center mb-3">
      <span class="text-muted text-xl">—</span>
    </div>
    <p class="font-medium text-ink">{{ title }}</p>
    <p v-if="description" class="text-sm text-muted mt-1 max-w-sm">{{ description }}</p>
    <div class="mt-4">
      <slot />
    </div>
  </div>
</template>
