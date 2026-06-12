<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-surface px-4">
    <div class="app-card text-center max-w-md">
      <p class="text-5xl font-semibold text-accent">404</p>
      <h1 class="text-lg font-semibold text-ink mt-2">Stranica ne postoji</h1>
      <p class="text-sm text-muted mt-2">
        Stranica koju tražite ne postoji ili je premještena.
      </p>
      <RouterLink :to="auth.isAuthenticated ? '/dashboard' : '/'" class="btn btn-primary mt-5 inline-flex">
        Povratak na početnu
      </RouterLink>
    </div>
  </div>
</template>
