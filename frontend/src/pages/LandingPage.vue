<script setup lang="ts">
import { RouterLink } from 'vue-router'

const features = [
  {
    title: 'Zajednički troškovi',
    description: 'Evidentirajte troškove kućanstva, podijelite ih među cimerima i pratite tko je što platio.',
  },
  {
    title: 'Dugovi na jednom mjestu',
    description: 'Automatski izračun tko kome koliko duguje, uz mogućnost označavanja dugova kao podmirenih.',
  },
  {
    title: 'Zadaci kućanstva',
    description: 'Dodijelite zadatke članovima kućanstva, postavite rokove i pratite status izvršenja.',
  },
  {
    title: 'Popis za kupnju',
    description: 'Vodite zajednički popis namirnica i potrepština te označavajte kupljene artikle.',
  },
  {
    title: 'Računi i potvrde',
    description: 'Učitajte sliku ili PDF računa uz svaki trošak kako bi sve ostalo transparentno.',
  },
  {
    title: 'Uloge i ovlasti',
    description: 'Korisnici, administratori i super administratori imaju prilagođene ovlasti unutar kućanstva.',
  },
]
</script>

<template>
  <div class="min-h-screen bg-surface">
    <header class="border-b border-secondary bg-white">
      <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 flex items-center justify-between">
        <span class="text-lg font-semibold text-ink">Planer kućanstva</span>
        <div class="flex items-center gap-2">
          <RouterLink to="/login" class="btn btn-secondary">Prijava</RouterLink>
          <RouterLink to="/register" class="btn btn-primary">Registracija</RouterLink>
        </div>
      </div>
    </header>

    <section class="max-w-6xl mx-auto px-4 md:px-6 py-16 text-center">
      <h1 class="text-3xl md:text-4xl font-semibold text-ink max-w-2xl mx-auto">
        Sve oko zajedničkog kućanstva, organizirano na jednom mjestu
      </h1>
      <p class="page-subtitle mt-4 max-w-xl mx-auto">
        Planer kućanstva pomaže cimerima i obiteljima da dijele troškove, zadatke i popis za
        kupnju bez nedoumica i prepiske.
      </p>
      <div class="mt-8 flex items-center justify-center gap-3">
        <RouterLink to="/register" class="btn btn-primary">Započni besplatno</RouterLink>
        <RouterLink to="/login" class="btn btn-secondary">Već imam račun</RouterLink>
      </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 md:px-6 pb-16">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="feature in features" :key="feature.title" class="app-card">
          <h3 class="font-semibold text-ink">{{ feature.title }}</h3>
          <p class="text-sm text-muted mt-2">{{ feature.description }}</p>
        </div>
      </div>
    </section>

    <footer class="border-t border-secondary bg-white">
      <div class="max-w-6xl mx-auto px-4 md:px-6 py-6 text-sm text-muted text-center">
        Planer kućanstva — studentski projekt iz kolegija razvoja web aplikacija.
      </div>
    </footer>
  </div>
</template>
