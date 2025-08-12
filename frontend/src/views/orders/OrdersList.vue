<template>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
      <h2 style="margin:0">Ordens de Serviço</h2>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input class="input" style="width:260px" v-model="q" placeholder="Buscar por cliente ou status" @keyup.enter="fetchItems" />
        <select class="input" v-model="status" style="width:160px">
          <option value="">Todos status</option>
          <option value="open">Aberta</option>
          <option value="in_progress">Em andamento</option>
          <option value="closed">Fechada</option>
        </select>
        <select class="input" v-model="sort" style="width:200px">
          <option value="sequence">Ordenar por Código (N/YY)</option>
          <option value="opened_at">Abertura</option>
          <option value="total">Total</option>
          <option value="status">Status</option>
        </select>
        <select class="input" v-model="order" style="width:140px">
          <option value="desc">Desc</option>
          <option value="asc">Asc</option>
        </select>
        <input class="input" type="date" v-model="from" />
        <input class="input" type="date" v-model="to" />
        <button class="button" :disabled="loading" @click="fetchItems">{{ loading ? 'Buscando…' : 'Buscar' }}</button>
        <RouterLink class="button" to="/service-orders/new">Nova OS</RouterLink>
      </div>
    </div>
    <div style="height:16px" />
    <table class="table">
      <thead>
        <tr>
          <th @click="toggleSort('sequence')" style="cursor:pointer">Código <span style="opacity:.7">{{ sortIcon('sequence') }}</span></th>
          <th @click="toggleSort('client')" style="cursor:pointer">Cliente <span style="opacity:.7">{{ sortIcon('client') }}</span></th>
          <th @click="toggleSort('status')" style="cursor:pointer">Status <span style="opacity:.7">{{ sortIcon('status') }}</span></th>
          <th @click="toggleSort('total')" style="cursor:pointer">Total <span style="opacity:.7">{{ sortIcon('total') }}</span></th>
          <th @click="toggleSort('opened_at')" style="cursor:pointer">Abertura <span style="opacity:.7">{{ sortIcon('opened_at') }}</span></th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading && items.length===0"><td colspan="5">Carregando…</td></tr>
        <tr v-if="!loading && items.length===0"><td colspan="5">Nenhuma OS encontrada.</td></tr>
        <tr v-for="o in items" :key="o.id" @click="goShow(o.id)" style="cursor:pointer">
          <td>{{ displayCode(o) }}</td>
          <td>{{ o.client?.name }}</td>
          <td>{{ o.status }}</td>
          <td>R$ {{ Number(o.total || 0).toFixed(2) }}</td>
          <td>{{ o.opened_at ? new Date(o.opened_at).toLocaleString() : '-' }}</td>
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
import { displayOrderCode } from '../../lib/format'

const items = ref<any[]>([])
const q = ref('')
const status = ref('')
const page = ref(1)
const perPage = ref(15)
const total = ref(0)
const from = ref('')
const to = ref('')
const sort = ref('sequence')
const order = ref<'asc'|'desc'>('desc')
const router = useRouter()
const loading = ref(false)

async function fetchItems(){
  loading.value = true
  try {
    const params: Record<string, any> = { page: page.value, per_page: perPage.value, sort: sort.value, order: order.value }
    if (q.value) params.q = q.value
    if (status.value) params.status = status.value
    if (from.value) params.from = from.value
    if (to.value) params.to = to.value
    const { data } = await api.get('/api/service-orders', { params })
    // API retorna paginator padrão Laravel
    items.value = data.data || data?.data
    total.value = data.total || 0
  } catch (e:any) {
    const { useToastStore } = await import('../../stores/toast')
    useToastStore().error(e?.response?.data?.message || 'Falha ao carregar lista de OS')
  } finally {
    loading.value = false
  }
}
function next(){ if(page.value * perPage.value < total.value){ page.value++; fetchItems() } }
function prev(){ if(page.value>1){ page.value--; fetchItems() } }
function goShow(id: number){ router.push(`/service-orders/${id}`) }
function toggleSort(col: string){
  // map 'client' to created_at fallback since backend doesn't sort by relation
  const backendCol = col === 'client' ? 'created_at' : col
  if (sort.value === backendCol) {
    order.value = order.value === 'asc' ? 'desc' : 'asc'
  } else {
    sort.value = backendCol
    order.value = 'asc'
  }
  fetchItems()
}
function sortIcon(col: string){
  const backendCol = col === 'client' ? 'created_at' : col
  if (sort.value !== backendCol) return ''
  return order.value === 'asc' ? '↑' : '↓'
}

onMounted(() => {
  const s = localStorage.getItem('orders.sort')
  const o = localStorage.getItem('orders.order') as 'asc'|'desc'|null
  if (s) sort.value = s
  if (o === 'asc' || o === 'desc') order.value = o
})
watch([sort, order], () => {
  localStorage.setItem('orders.sort', sort.value)
  localStorage.setItem('orders.order', order.value)
})

const displayCode = displayOrderCode

onMounted(fetchItems)
</script>
