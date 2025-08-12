<template>
  <div class="card" v-if="loaded">
    <h2 style="margin-top:0">Editar Produto</h2>
    <form @submit.prevent="submit" style="display:grid;gap:12px;max-width:640px">
      <UIInput label="Nome" v-model="form.name" :error="errors.name?.[0]" />
      <UIInput label="SKU" v-model="form.sku" :error="errors.sku?.[0]" />
      <UIInput label="Descrição" v-model="form.description" />
      <UIInput label="Preço" type="number" step="0.01" v-model="form.price" :error="errors.price?.[0]" />
      <UIInput label="Estoque" type="number" v-model="form.stock" :error="errors.stock?.[0]" />
      <div style="display:flex;gap:8px;margin-top:8px">
        <UIButton :disabled="loading">Salvar</UIButton>
        <RouterLink class="button" style="background:#334155" :to="`/products/${id}`">Cancelar</RouterLink>
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
import { reactive, ref, onMounted } from 'vue'
import UIInput from '../../components/UIInput.vue'
import UIButton from '../../components/UIButton.vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../lib/api'

const route = useRoute()
const router = useRouter()
const id = Number(route.params.id)
const form = reactive({ name: '', sku: '', description: '', price: 0, stock: 0 })
const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})
const loaded = ref(false)

async function load(){
  const { data } = await api.get(`/api/products/${id}`)
  Object.assign(form, data.data)
  loaded.value = true
}

async function submit(){
  loading.value = true
  error.value = ''
  try{
    await api.put(`/api/products/${id}`, form)
    router.push(`/products/${id}`)
  }catch(e:any){
    error.value = e?.response?.data?.message || 'Erro ao salvar'
    errors.value = e?.response?.data?.errors || {}
  }finally{
    loading.value = false
  }
}

onMounted(load)
</script>
