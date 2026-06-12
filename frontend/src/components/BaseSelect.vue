<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string | number | null
    label?: string
    options: { value: string | number; label: string }[]
    placeholder?: string
  }>(),
  {
    placeholder: 'Odaberi...',
  }
)

defineEmits<{ 'update:modelValue': [value: string] }>()
</script>

<template>
  <div>
    <label v-if="label" class="form-label">{{ label }}</label>
    <select
      class="form-select"
      :value="modelValue ?? ''"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <option value="">{{ placeholder }}</option>
      <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </select>
  </div>
</template>
