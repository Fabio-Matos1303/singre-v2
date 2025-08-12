<template>
  <div class="card">
    <h2 style="margin-top:0">Novo Cliente</h2>
    <form @submit.prevent="submit" style="display:grid;gap:12px;max-width:640px">
      <UIInput label="Nome" v-model="form.name" :error="errors.name?.[0]" />
      <UIInput label="Email" type="email" v-model="form.email" :error="errors.email?.[0]" />
      <label>Telefone</label>
      <UIInput v-model="form.phone" />
      <label>Documento</label>
      <UIInput v-model="form.document" />
      <label>Endereço</label>
      <UIInput v-model="form.address" />
      <div style="display:flex;gap:8px;margin-top:8px">
        <UIButton :disabled="loading">Salvar</UIButton>
        <RouterLink class="button" style="background:#334155" to="/clients">Cancelar</RouterLink>
      </div>
      <p v-if="error" style="color:#fca5a5">{{ error }}</p>
      <ul v-if="Object.keys(errors).length" style="color:#fca5a5">
        <li v-for="(msgs, field) in errors" :key="field">
          {{ field }}: {{ (msgs as string[]).join(', ') }}
        </li>
      </ul>
    </form>
  </div>
</template>
<script setup lang="ts">
import { reactive, ref } from 'vue'
import UIInput from '../../components/UIInput.vue'
import UIButton from '../../components/UIButton.vue'
import { useRouter } from 'vue-router'
import api from '../../lib/api'

const router = useRouter()
const form = reactive({ name: '', email: '', phone: '', document: '', address: '' })
const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await api.post('/api/clients', form)
    router.push('/clients')
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Erro ao salvar'
    errors.value = e?.response?.data?.errors || {}
  } finally {
    loading.value = false
  }
}
</script>
