<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseBadge from './BaseBadge.vue'

const emit = defineEmits<{ 'toggle-sidebar': [] }>()

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const title = computed(() => (route.meta.title as string) || 'Planer kućanstva')

const roleLabel = computed(() => {
  switch (auth.user?.role) {
    case 'super_admin':
      return 'Super admin'
    case 'admin':
      return 'Administrator'
    default:
      return 'Korisnik'
  }
})

const isMobile = ref(false)
let mediaQuery: MediaQueryList | null = null
const updateMobile = () => {
  isMobile.value = !!mediaQuery?.matches
}

onMounted(() => {
  mediaQuery = window.matchMedia('(max-width: 767px)')
  updateMobile()
  mediaQuery.addEventListener('change', updateMobile)
})

onBeforeUnmount(() => {
  mediaQuery?.removeEventListener('change', updateMobile)
})

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <header class="sticky top-0 z-20 bg-white border-b border-secondary">
    <div class="flex items-center justify-between gap-3 px-4 md:px-6 py-3">
      <div class="flex items-center gap-3">
        <button
          v-if="isMobile"
          class="btn btn-ghost px-2"
          aria-label="Otvori izbornik"
          @click="emit('toggle-sidebar')"
        >
          ☰
        </button>
        <h1 class="page-title">{{ title }}</h1>
      </div>

      <div class="flex items-center gap-3">
        <BaseBadge variant="default" class="hidden sm:inline-flex">{{ roleLabel }}</BaseBadge>
        <span class="hidden sm:inline text-sm text-muted">{{ auth.user?.name }}</span>
        <button class="btn btn-secondary" @click="logout">Odjava</button>
      </div>
    </div>
  </header>
</template>
