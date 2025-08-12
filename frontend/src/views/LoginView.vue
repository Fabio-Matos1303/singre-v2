<template>
  <div class="container">
    <div class="card" style="max-width:420px;margin:64px auto;">
      <h1 style="margin:0 0 16px 0;">Entrar</h1>
      <form @submit.prevent="submit">
        <UIInput label="Email" type="email" v-model="email" :error="errors.email?.[0]" />
        <div style="height:12px" />
        <UIInput label="Senha" type="password" v-model="password" :error="errors.password?.[0]" />
        <div style="height:16px" />
        <UIButton :disabled="loading">
          <span v-if="!loading">Entrar</span>
          <span v-else>Entrando…</span>
        </UIButton>
      </form>
      <div style="height:8px" />
      <RouterLink class="button" style="background:#334155" to="/register">Criar conta</RouterLink>
      <p v-if="error" style="color:#fca5a5;margin-top:12px">{{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import UIButton from '../components/UIButton.vue'
import UIInput from '../components/UIInput.vue'

const email = ref('admin@example.com')
// senha seedada no backend
const password = ref('secret1234')
const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})
const router = useRouter()
const auth = useAuthStore()

async function submit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  try {
    await auth.login(email.value, password.value)
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Falha no login'
    errors.value = e?.response?.data?.errors || {}
  } finally {
    loading.value = false
  }
}
</script>
