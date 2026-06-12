<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps<{ open: boolean }>()
const emit = defineEmits<{ close: [] }>()

const auth = useAuthStore()
const route = useRoute()

interface NavLink {
  to: string
  label: string
}

const mainLinks: NavLink[] = [
  { to: '/dashboard', label: 'Nadzorna ploča' },
  { to: '/household', label: 'Kućanstvo' },
  { to: '/expenses', label: 'Troškovi' },
  { to: '/debts', label: 'Dugovi' },
  { to: '/tasks', label: 'Zadaci' },
  { to: '/shopping', label: 'Popis za kupnju' },
  { to: '/upload-receipt', label: 'Učitaj račun' },
]

const adminLinks: NavLink[] = [
  { to: '/admin/users', label: 'Korisnici' },
]

function isActive(to: string) {
  return route.path === to || route.path.startsWith(to + '/')
}
</script>

<template>
  <!-- Mobile overlay -->
  <div v-if="open" class="fixed inset-0 bg-ink/40 z-30 md:hidden" @click="emit('close')" />

  <aside
    class="fixed md:static inset-y-0 left-0 z-40 w-60 shrink-0 bg-panel text-white flex flex-col transition-transform duration-200 md:translate-x-0"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
  >
    <div class="px-5 py-5 border-b border-white/10">
      <p class="text-lg font-semibold leading-tight">Planer kućanstva</p>
      <p class="text-xs text-secondary mt-0.5">Organizacija doma</p>
    </div>

    <nav class="flex-1 overflow-y-auto py-3">
      <p class="px-5 pt-2 pb-1 text-xs uppercase tracking-wide text-secondary">Izbornik</p>
      <RouterLink
        v-for="link in mainLinks"
        :key="link.to"
        :to="link.to"
        class="flex items-center gap-3 px-5 py-2.5 text-sm border-l-2 transition-colors"
        :class="
          isActive(link.to)
            ? 'border-accent bg-white/5 text-white font-medium'
            : 'border-transparent text-secondary hover:text-white hover:bg-white/5'
        "
        @click="emit('close')"
      >
        {{ link.label }}
      </RouterLink>

      <template v-if="auth.hasRole('admin', 'super_admin')">
        <p class="px-5 pt-4 pb-1 text-xs uppercase tracking-wide text-secondary">Administracija</p>
        <RouterLink
          v-for="link in adminLinks"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-3 px-5 py-2.5 text-sm border-l-2 transition-colors"
          :class="
            isActive(link.to)
              ? 'border-accent bg-white/5 text-white font-medium'
              : 'border-transparent text-secondary hover:text-white hover:bg-white/5'
          "
          @click="emit('close')"
        >
          {{ link.label }}
        </RouterLink>
      </template>
    </nav>

    <div class="px-5 py-4 border-t border-white/10 text-xs text-secondary">
      Prijavljeni kao
      <p class="text-white font-medium truncate">{{ auth.user?.name }}</p>
    </div>
  </aside>
</template>
