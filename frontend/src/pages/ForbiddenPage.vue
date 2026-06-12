<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-surface px-4">
    <div class="app-card text-center max-w-md">
      <p class="text-5xl font-semibold text-accent">403</p>
      <h1 class="text-lg font-semibold text-ink mt-2">Nemate pristup ovoj stranici</h1>
      <p class="text-sm text-muted mt-2">
        Vaša korisnička ulogа ne dopušta pregled ovog sadržaja. Ako smatrate da je to greška,
        obratite se administratoru kućanstva.
      </p>
      <RouterLink to="/dashboard" class="btn btn-primary mt-5 inline-flex">Povratak na nadzornu ploču</RouterLink>
    </div>
  </div>
</template>
