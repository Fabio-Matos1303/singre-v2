<template>
  <div class="card">
    <h2 style="margin-top:0">Novo Equipamento</h2>
    <form @submit.prevent="submit" style="display:grid;gap:12px;max-width:800px">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div style="position:relative">
          <label>Cliente</label>
          <UIInput v-model="clientQuery" placeholder="Digite para buscar cliente" @input="debouncedSearchClients" />
          <ul v-if="showClientDropdown" style="position:absolute;z-index:10;left:0;right:0;top:70px;background:#0f172a;border:1px solid rgba(255,255,255,0.12);border-radius:8px;max-height:220px;overflow:auto;padding:6px;margin:0;list-style:none">
            <li v-for="c in clientOptions" :key="c.id" @click="selectClient(c)" style="padding:8px;border-radius:6px;cursor:pointer">
              {{ c.name }} <span style="color:#94a3b8">({{ c.email }})</span>
            </li>
            <li v-if="clientLoading" style="padding:8px;color:#94a3b8">Buscando…</li>
            <li v-if="!clientLoading && clientOptions.length===0" style="padding:8px;color:#94a3b8">Nenhum resultado</li>
          </ul>
          <div v-if="form.client_id" style="margin-top:6px;color:#94a3b8">Selecionado: {{ selectedClientLabel }}</div>
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
        <UIButton :disabled="loading">Criar</UIButton>
        <RouterLink class="button" style="background:#334155" to="/equipment">Cancelar</RouterLink>
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
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../lib/api'

const router = useRouter()
const clientQuery = ref('')
const clientOptions: any = ref([])
const clientLoading = ref(false)
const showClientDropdown = computed(() => clientQuery.value.length > 1)

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

const selectedClientLabel = computed(() => {
  const c = clientOptions.value.find((x: any) => x.id === form.client_id)
  return c ? `${c.name} (${c.email})` : ''
})

function debounce<T extends (...args: any[]) => any>(fn: T, delay = 300){
  let t: any
  return (...args: any[]) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay) }
}

async function searchClients(){
  clientLoading.value = true
  try{
    const { data } = await api.get('/api/clients', { params: { q: clientQuery.value, per_page: 10 } })
    clientOptions.value = data.data
  } finally { clientLoading.value = false }
}
const debouncedSearchClients = debounce(searchClients, 350)
function selectClient(c: any){ form.client_id = c.id; if(!clientOptions.value.find((x:any)=>x.id===c.id)) clientOptions.value.push(c); clientQuery.value = c.name }

async function submit(){
  loading.value = true
  error.value = ''
  errors.value = {}
  try{
    const payload = { ...form }
    const { data } = await api.post('/api/equipment', payload)
    router.push(`/equipment/${data.id}`)
  }catch(e:any){
    error.value = e?.response?.data?.message || 'Erro ao criar equipamento'
    errors.value = e?.response?.data?.errors || {}
  }finally{
    loading.value = false
  }
}
</script>
