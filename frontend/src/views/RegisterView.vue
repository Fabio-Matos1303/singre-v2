<template>
  <div class="container">
    <div class="card" style="max-width:420px;margin:64px auto;">
      <h1 style="margin:0 0 16px 0;">Criar conta</h1>
      <form @submit.prevent="submit">
        <UIInput label="Nome" v-model="name" :error="errors.name?.[0]" />
        <div style="height:12px" />
        <UIInput label="Email" type="email" v-model="email" :error="errors.email?.[0]" />
        <div style="height:12px" />
        <UIInput label="Senha" type="password" v-model="password" :error="errors.password?.[0] || passwordError" />
        <div style="height:16px" />
        <UIButton :disabled="loading">Cadastrar</UIButton>
        <RouterLink class="button" style="background:#334155;margin-left:8px" to="/login">Já tenho conta</RouterLink>
      </form>
      <p v-if="error" style="color:#fca5a5;margin-top:12px">{{ error }}</p>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../lib/api'
import UIButton from '../components/UIButton.vue'
import UIInput from '../components/UIInput.vue'

const name = ref('')
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})
const router = useRouter()
const passwordError = computed(() => password.value.length>0 && password.value.length<8 ? 'Mínimo de 8 caracteres' : '')

async function submit(){
  loading.value = true
  error.value = ''
  errors.value = {}
  try{
    await api.post('/api/auth/register', { name: name.value, email: email.value, password: password.value })
    // após registro, tenta login automático
    const login = await api.post('/api/auth/login', { email: email.value, password: password.value })
    localStorage.setItem('auth_token', login.data.token)
    router.push('/dashboard')
  }catch(e:any){
    error.value = e?.response?.data?.message || 'Erro no cadastro'
    errors.value = e?.response?.data?.errors || {}
  }finally{
    loading.value = false
  }
}
</script>
