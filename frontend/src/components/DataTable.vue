<script setup lang="ts">
export interface DataTableColumn {
  key: string
  label: string
  align?: 'left' | 'right' | 'center'
}

defineProps<{
  columns: DataTableColumn[]
  rows: Record<string, any>[]
  loading?: boolean
}>()
</script>

<template>
  <div class="overflow-x-auto">
    <table class="data-table">
      <thead>
        <tr>
          <th v-for="col in columns" :key="col.key" :style="col.align ? `text-align:${col.align}` : ''">
            {{ col.label }}
          </th>
          <th v-if="$slots.actions" style="text-align:right">Akcije</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="text-center text-muted py-6">
            Učitavanje...
          </td>
        </tr>
        <tr v-else-if="rows.length === 0">
          <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="text-center text-muted py-6">
            Nema podataka za prikaz.
          </td>
        </tr>
        <tr v-for="(row, idx) in rows" :key="row.id ?? idx">
          <td v-for="col in columns" :key="col.key" :style="col.align ? `text-align:${col.align}` : ''">
            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
              {{ row[col.key] }}
            </slot>
          </td>
          <td v-if="$slots.actions" style="text-align:right">
            <slot name="actions" :row="row" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
