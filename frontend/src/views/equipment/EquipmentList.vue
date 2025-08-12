<template>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
      <h2 style="margin:0">Equipamentos</h2>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input class="input" style="width:260px" v-model="q" placeholder="Buscar por série, marca ou modelo" @keyup.enter="fetchItems" />
        <select class="input" v-model="clientId" style="width:220px">
          <option value="0">Todos clientes</option>
          <option v-for="c in clientOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select class="input" v-model="sort" style="width:200px">
          <option value="id">ID</option>
          <option value="serial_company">Série (Empresa)</option>
          <option value="serial_manufacturer">Série (Fabricante)</option>
          <option value="brand">Marca</option>
          <option value="model">Modelo</option>
          <option value="intervention_count">Intervenções</option>
        </select>
        <select class="input" v-model="order" style="width:140px">
          <option value="desc">Desc</option>
          <option value="asc">Asc</option>
        </select>
        <button class="button" :disabled="loading" @click="fetchItems">{{ loading ? 'Buscando…' : 'Buscar' }}</button>
        <RouterLink class="button" to="/equipment/new">Novo</RouterLink>
      </div>
    </div>

    <div style="height:16px" />

    <table class="table">
      <thead>
        <tr>
          <th @click="toggleSort('id')" style="cursor:pointer">ID <span style="opacity:.7">{{ sortIcon('id') }}</span></th>
          <th>
            <span @click="toggleSort('serial_company')" style="cursor:pointer">Série (Empresa) <span style="opacity:.7">{{ sortIcon('serial_company') }}</span></span>
            /
            <span @click="toggleSort('serial_manufacturer')" style="cursor:pointer">Série (Fabricante) <span style="opacity:.7">{{ sortIcon('serial_manufacturer') }}</span></span>
          </th>
          <th>
            <span @click="toggleSort('brand')" style="cursor:pointer">Marca <span style="opacity:.7">{{ sortIcon('brand') }}</span></span>
            /
            <span @click="toggleSort('model')" style="cursor:pointer">Modelo <span style="opacity:.7">{{ sortIcon('model') }}</span></span>
          </th>
          <th>Cliente</th>
          <th @click="toggleSort('intervention_count')" style="cursor:pointer">Intervenções <span style="opacity:.7">{{ sortIcon('intervention_count') }}</span></th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading && items.length===0"><td colspan="5">Carregando…</td></tr>
        <tr v-if="!loading && items.length===0"><td colspan="5">Nenhum equipamento encontrado.</td></tr>
        <tr v-for="e in items" :key="e.id" @click="goShow(e.id)" style="cursor:pointer">
          <td>#{{ e.id }}</td>
          <td>{{ e.serial_company || '-' }} / {{ e.serial_manufacturer || '-' }}</td>
          <td>{{ e.brand || '-' }} {{ e.model || '' }}</td>
          <td>{{ e.client?.name }}</td>
          <td>{{ e.intervention_count }}</td>
        </tr>
      </tbody>
    </table>

    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
      <button class="button" :disabled="page===1 || loading" @click="prev">Anterior</button>
      <div style="display:flex;align-items:center;gap:8px;color:#94a3b8">
        <span>Página {{ page }} de {{ Math.max(1, Math.ceil(total / perPage)) }}</span>
      </div>
      <button class="button" :disabled="page * perPage >= total || loading" @click="next">Próxima</button>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../lib/api'

const items = ref<any[]>([])
const clientOptions = ref<any[]>([])
const q = ref('')
const clientId = ref(0)
const page = ref(1)
const perPage = ref(15)
const total = ref(0)
const router = useRouter()
const loading = ref(false)
const sort = ref('id')
const order = ref<'asc'|'desc'>('desc')

async function loadClients(){
  const { data } = await api.get('/api/clients', { params: { per_page: 200 } })
  clientOptions.value = data.data || data
}

async function fetchItems(){
  loading.value = true
  try{
    const params: Record<string, any> = { page: page.value, per_page: perPage.value, sort: sort.value, order: order.value }
    if (q.value) params.q = q.value
    if (clientId.value) params.client_id = clientId.value
    const { data } = await api.get('/api/equipment', { params })
    items.value = data.data || []
    total.value = data.total || 0
  } finally { loading.value = false }
}
function next(){ if(page.value * perPage.value < total.value){ page.value++; fetchItems() } }
function prev(){ if(page.value>1){ page.value--; fetchItems() } }
function goShow(id: number){ router.push(`/equipment/${id}`) }
function toggleSort(col: string){
  if (sort.value === col) {
    order.value = order.value === 'asc' ? 'desc' : 'asc'
  } else {
    sort.value = col
    order.value = 'asc'
  }
  fetchItems()
}
function sortIcon(col: string){
  if (sort.value !== col) return ''
  return order.value === 'asc' ? '↑' : '↓'
}

onMounted(() => { loadClients(); fetchItems() })
watch([sort, order], () => {
  localStorage.setItem('equipment.sort', sort.value)
  localStorage.setItem('equipment.order', order.value)
})
onMounted(() => {
  const s = localStorage.getItem('equipment.sort')
  const o = localStorage.getItem('equipment.order') as 'asc'|'desc'|null
  if (s) sort.value = s
  if (o === 'asc' || o === 'desc') order.value = o
})
</script>
