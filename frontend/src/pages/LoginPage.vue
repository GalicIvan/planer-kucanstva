<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseInput from '@/components/BaseInput.vue'
import BaseButton from '@/components/BaseButton.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')

async function onSubmit() {
  const ok = await auth.login(email.value, password.value)
  if (ok) {
    const redirect = (route.query.redirect as string) || '/dashboard'
    router.push(redirect)
  }
}
</script>

<template>
  <div>
    <h2 class="text-lg font-semibold text-ink mb-1">Prijava</h2>
    <p class="text-sm text-muted mb-5">Prijavite se sa svojim podacima za pristup planeru.</p>

    <form class="space-y-4" @submit.prevent="onSubmit">
      <BaseInput v-model="email" type="email" label="E-mail" required placeholder="ime@primjer.com" />
      <BaseInput v-model="password" type="password" label="Lozinka" required placeholder="••••••••" />

      <p v-if="auth.error" class="text-sm text-accent">{{ auth.error }}</p>

      <BaseButton type="submit" variant="primary" :disabled="auth.loading" class="w-full">
        {{ auth.loading ? 'Prijava u tijeku...' : 'Prijavi se' }}
      </BaseButton>
    </form>

    <p class="text-sm text-muted text-center mt-5">
      Nemate račun?
      <RouterLink to="/register" class="text-accent font-medium">Registrirajte se</RouterLink>
    </p>

    <div class="mt-6 pt-4 border-t border-secondary text-xs text-muted">
      <p class="font-medium text-ink mb-1">Testni korisnici:</p>
      <p>superadmin@planer.test / password</p>
      <p>admin@planer.test / password</p>
      <p>ana@planer.test / password</p>
      <p>marko@planer.test / password</p>
    </div>
  </div>
</template>
