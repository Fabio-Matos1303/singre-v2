<template>
  <div class="card" v-if="loaded">
    <h2 style="margin-top:0">Editar Equipamento</h2>
    <form @submit.prevent="submit" style="display:grid;gap:12px;max-width:800px">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label>Cliente</label>
          <div class="input" style="background:transparent;display:flex;align-items:center;min-height:44px">
            <span>{{ selectedClientLabel }}</span>
          </div>
        </div>
        <div>
          <label>Marca</label>
          <UIInput v-model="form.brand" />
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label>Modelo</label>
          <UIInput v-model="form.model" />
        </div>
        <div>
          <label>Série da empresa</label>
          <UIInput v-model="form.serial_company" />
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label>Série do fabricante</label>
          <UIInput v-model="form.serial_manufacturer" />
        </div>
        <div>
          <label>Configuração</label>
          <textarea class="input" v-model="form.configuration" rows="3" />
        </div>
      </div>

      <div style="display:flex;gap:8px;justify-content:flex-end">
        <UIButton :disabled="loading" :loading="loading">Salvar</UIButton>
        <RouterLink class="button" style="background:#334155" :to="`/equipment/${id}`">Cancelar</RouterLink>
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
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../lib/api'

const route = useRoute()
const router = useRouter()
const id = Number(route.params.id)
const loaded = ref(false)

const form = reactive({
  client_id: 0,
  serial_company: '',
  serial_manufacturer: '',
  brand: '',
  model: '',
  configuration: '',
})

const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})

const selectedClientLabel = computed(() => `${form.client_id}`)

async function load(){
  const { data } = await api.get(`/api/equipment/${id}`)
  form.client_id = data.client_id
  form.serial_company = data.serial_company || ''
  form.serial_manufacturer = data.serial_manufacturer || ''
  form.brand = data.brand || ''
  form.model = data.model || ''
  form.configuration = data.configuration || ''
  loaded.value = true
}

async function submit(){
  loading.value = true
  error.value = ''
  errors.value = {}
  try{
    const payload = { ...form }
    await api.put(`/api/equipment/${id}`, payload)
    router.push(`/equipment/${id}`)
  }catch(e:any){
    error.value = e?.response?.data?.message || 'Erro ao salvar'
    errors.value = e?.response?.data?.errors || {}
  }finally{
    loading.value = false
  }
}

onMounted(load)
</script>
