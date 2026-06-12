<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string | number | null
    label?: string
    type?: string
    placeholder?: string
    required?: boolean
    error?: string
  }>(),
  {
    type: 'text',
    required: false,
  }
)

defineEmits<{ 'update:modelValue': [value: string] }>()
</script>

<template>
  <div>
    <label v-if="label" class="form-label">{{ label }}<span v-if="required" class="text-accent"> *</span></label>
    <input
      :type="type"
      class="form-input"
      :placeholder="placeholder"
      :required="required"
      :value="modelValue ?? ''"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <p v-if="error" class="text-accent text-xs mt-1">{{ error }}</p>
  </div>
</template>
