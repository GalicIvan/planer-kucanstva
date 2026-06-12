<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-surface px-4 py-10">
    <div class="w-full max-w-md">
      <div class="text-center mb-6">
        <RouterLink to="/" class="text-xl font-semibold text-ink">Planer kućanstva</RouterLink>
        <p class="page-subtitle mt-1">Organizirajte troškove, zadatke i kupovinu zajedno</p>
      </div>
      <div class="app-card">
        <router-view />
      </div>
    </div>
  </div>
</template>
