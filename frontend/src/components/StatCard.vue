<script setup lang="ts">
defineProps<{
  label: string
  value: string | number
  hint?: string
  dark?: boolean
}>()
</script>

<template>
  <div :class="dark ? 'app-card-dark' : 'app-card'">
    <p :class="dark ? 'text-sm text-secondary' : 'page-subtitle'">{{ label }}</p>
    <p class="text-2xl font-semibold mt-1" :class="dark ? 'text-white' : 'text-ink'">{{ value }}</p>
    <p v-if="hint" class="text-xs mt-1" :class="dark ? 'text-secondary' : 'text-muted'">{{ hint }}</p>
  </div>
</template>
