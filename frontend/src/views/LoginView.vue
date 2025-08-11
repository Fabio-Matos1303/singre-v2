<template>
  <div class="container">
    <div class="card" style="max-width:420px;margin:64px auto;">
      <h1 style="margin:0 0 16px 0;">Entrar</h1>
      <form @submit.prevent="submit">
        <label>Email</label>
        <input class="input" v-model="email" type="email" required />
        <div style="height:12px" />
        <label>Senha</label>
        <input class="input" v-model="password" type="password" required />
        <div style="height:16px" />
        <button class="button" :disabled="loading">
          <span v-if="!loading">Entrar</span>
          <span v-else>Entrando…</span>
        </button>
      </form>
      <p v-if="error" style="color:#fca5a5;margin-top:12px">{{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const email = ref('admin@example.com')
const password = ref('password')
const loading = ref(false)
const error = ref('')
const router = useRouter()

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await axios.post('/api/auth/login', { email: email.value, password: password.value })
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Falha no login'
  } finally {
    loading.value = false
  }
}
</script>
