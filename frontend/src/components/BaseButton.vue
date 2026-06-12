<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
    type?: 'button' | 'submit'
    disabled?: boolean
  }>(),
  {
    variant: 'primary',
    type: 'button',
    disabled: false,
  }
)
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="[
      'btn',
      variant === 'primary' && 'btn-primary',
      variant === 'secondary' && 'btn-secondary',
      variant === 'danger' && 'btn-danger',
      variant === 'ghost' && 'btn-ghost',
    ]"
  >
    <slot />
  </button>
</template>
