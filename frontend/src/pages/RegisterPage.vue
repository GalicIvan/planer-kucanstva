<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseInput from '@/components/BaseInput.vue'
import BaseButton from '@/components/BaseButton.vue'

const auth = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')

async function onSubmit() {
  const ok = await auth.register(name.value, email.value, password.value, passwordConfirmation.value)
  if (ok) {
    router.push('/dashboard')
  }
}
</script>

<template>
  <div>
    <h2 class="text-lg font-semibold text-ink mb-1">Registracija</h2>
    <p class="text-sm text-muted mb-5">Otvorite novi korisnički račun u nekoliko sekundi.</p>

    <form class="space-y-4" @submit.prevent="onSubmit">
      <BaseInput v-model="name" label="Ime i prezime" required placeholder="Ana Anić" />
      <BaseInput v-model="email" type="email" label="E-mail" required placeholder="ime@primjer.com" />
      <BaseInput v-model="password" type="password" label="Lozinka" required placeholder="••••••••" />
      <BaseInput
        v-model="passwordConfirmation"
        type="password"
        label="Potvrda lozinke"
        required
        placeholder="••••••••"
      />

      <p v-if="auth.error" class="text-sm text-accent">{{ auth.error }}</p>

      <BaseButton type="submit" variant="primary" :disabled="auth.loading" class="w-full">
        {{ auth.loading ? 'Stvaranje računa...' : 'Registriraj se' }}
      </BaseButton>
    </form>

    <p class="text-sm text-muted text-center mt-5">
      Već imate račun?
      <RouterLink to="/login" class="text-accent font-medium">Prijavite se</RouterLink>
    </p>
  </div>
</template>
