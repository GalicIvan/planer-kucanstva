<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'default' | 'success' | 'pending'
  }>(),
  { variant: 'default' }
)
</script>

<template>
  <span
    :class="[
      'badge',
      variant === 'default' && 'badge-default',
      variant === 'success' && 'badge-success',
      variant === 'pending' && 'badge-pending',
    ]"
  >
    <slot />
  </span>
</template>
