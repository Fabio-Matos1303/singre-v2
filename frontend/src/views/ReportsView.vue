<template>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
      <h2 style="margin:0">Relatórios</h2>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input class="input" style="width:260px" v-model="q" placeholder="Buscar por cliente ou status" @keyup.enter="fetchItems" />
        <select class="input" v-model="status" style="width:160px">
          <option value="">Todos status</option>
          <option value="open">Aberta</option>
          <option value="in_progress">Em andamento</option>
          <option value="closed">Fechada</option>
        </select>
        <input class="input" type="date" v-model="from" />
        <input class="input" type="date" v-model="to" />
        <select class="input" v-model="interval" style="width:140px">
          <option value="day">Dia</option>
          <option value="week">Semana</option>
        </select>
        <button class="button" @click="fetchItems" :disabled="loading">{{ loading ? 'Buscando…' : 'Buscar' }}</button>
      </div>
    </div>

    <div style="height:16px" />

    <div class="card" style="padding:16px;margin-bottom:16px">
      <h3 style="margin-top:0">Resumo</h3>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
        <div class="card">
          <div style="color:#94a3b8">Pedidos</div>
          <div style="font-size:28px;font-weight:600">{{ summary.orders?.count || 0 }}</div>
        </div>
        <div class="card">
          <div style="color:#94a3b8">Receita</div>
          <div style="font-size:28px;font-weight:600">R$ {{ Number(summary.orders?.total || 0).toFixed(2) }}</div>
        </div>
        <div class="card">
          <div style="color:#94a3b8">Ticket médio</div>
          <div style="font-size:28px;font-weight:600">R$ {{ avgOrderValue.toFixed(2) }}</div>
        </div>
      </div>
    </div>

    <div class="card" style="padding:16px;margin:16px 0">
      <h3 style="margin-top:0">Série temporal (Pedidos e Receita)</h3>
      <LineChart v-if="tsConfig" :config="tsConfig" />
    </div>

    <div class="card" style="padding:16px;margin:16px 0">
      <h3 style="margin-top:0">Série por Status</h3>
      <LineChart v-if="statusConfig" :config="statusConfig" />
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Código</th>
          <th>Cliente</th>
          <th>Status</th>
          <th>Total</th>
          <th>Abertura</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="o in items" :key="o.id">
          <td>{{ displayCode(o) }}</td>
          <td>{{ o.client?.name }}</td>
          <td>{{ o.status }}</td>
          <td>R$ {{ Number(o.total || 0).toFixed(2) }}</td>
          <td>{{ o.opened_at ? new Date(o.opened_at).toLocaleString() : '-' }}</td>
          <td>
            <a class="button" style="background:#334155" :href="`/api/service-orders/${o.id}/pdf`" target="_blank">PDF</a>
            <RouterLink class="button" style="background:#1d4ed8" :to="`/service-orders/${o.id}`">Abrir</RouterLink>
          </td>
        </tr>
      </tbody>
    </table>

    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
      <button class="button" :disabled="page===1" @click="prev">Anterior</button>
      <div style="display:flex;align-items:center;gap:8px;color:#94a3b8">
        <span>Página {{ page }} de {{ Math.max(1, Math.ceil(total / perPage)) }}</span>
      </div>
      <button class="button" :disabled="page * perPage >= total" @click="next">Próxima</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '../lib/api'
import { displayOrderCode } from '../lib/format'
import LineChart from '../components/LineChart.vue'

const items = ref<any[]>([])
const loading = ref(false)
const q = ref('')
const status = ref('')
const page = ref(1)
const perPage = ref(15)
const total = ref(0)
const from = ref('')
const to = ref('')
const interval = ref<'day'|'week'>('day')

const summary = ref<any>({ orders: { count: 0, total: 0 } })
const avgOrderValue = computed(() => {
  const count = Number(summary.value?.orders?.count || 0)
  const totalSum = Number(summary.value?.orders?.total || 0)
  return count > 0 ? totalSum / count : 0
})

const tsConfig = ref<any>(null)
const statusConfig = ref<any>(null)

async function fetchSummary(){
  const params: Record<string, any> = {}
  if (from.value) params.from = from.value
  if (to.value) params.to = to.value
  const { data } = await api.get('/api/reports/summary', { params })
  summary.value = data
}

async function fetchItems(){
  loading.value = true
  try{
    const params: Record<string, any> = { page: page.value, per_page: perPage.value }
    if (q.value) params.q = q.value
    if (status.value) params.status = status.value
    if (from.value) params.from = from.value
    if (to.value) params.to = to.value
    const { data } = await api.get('/api/service-orders', { params })
    items.value = data.data || []
    total.value = data.total || 0
    await fetchSummary()
  }catch(e:any){
    const { useToastStore } = await import('../stores/toast')
    useToastStore().error(e?.response?.data?.message || 'Falha ao carregar relatórios')
  }finally{
    loading.value = false
  }
}

async function loadTimeseries(){
  const params: Record<string, any> = {}
  if (from.value) params.from = from.value
  if (to.value) params.to = to.value
  if (interval.value) params.interval = interval.value
  const { data } = await api.get('/api/reports/timeseries', { params })
  const labels = (data.series || []).map((r:any) => r.date || r.year_week)
  const orders = (data.series || []).map((r:any) => r.orders)
  const revenue = (data.series || []).map((r:any) => r.revenue)
  tsConfig.value = {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Pedidos', data: orders, borderColor: '#60a5fa', backgroundColor: 'rgba(96,165,250,0.2)', tension: 0.25 },
        { label: 'Receita', data: revenue, yAxisID: 'y1', borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.2)', tension: 0.25 },
      ],
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      stacked: false,
      plugins: { legend: { labels: { color: '#e5e7eb' } } },
      scales: {
        x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.2)' } },
        y: { type: 'linear', position: 'left', ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.2)' } },
        y1: { type: 'linear', position: 'right', ticks: { color: '#94a3b8' }, grid: { drawOnChartArea: false } },
      },
    },
  }
}

async function loadStatusSeries(){
  const params: Record<string, any> = {}
  if (from.value) params.from = from.value
  if (to.value) params.to = to.value
  if (interval.value) params.interval = interval.value
  const { data } = await api.get('/api/reports/status-series', { params })
  const labels = (data.series || []).map((r:any) => r.bucket)
  const allStatuses = new Set<string>()
  for (const row of (data.series || [])) {
    const statuses = row.statuses || {}
    Object.keys(statuses).forEach(s => allStatuses.add(s))
  }
  const palette: Record<string,string> = {
    open: '#60a5fa',
    in_progress: '#f59e0b',
    closed: '#22c55e',
  }
  const datasets = Array.from(allStatuses).map((s, i) => {
    const color = palette[s] || ['#f87171','#a78bfa','#f472b6','#34d399','#fde047','#38bdf8','#fb7185'][i % 7]
    return {
      label: s,
      data: (data.series || []).map((r:any) => (r.statuses || {})[s] || 0),
      borderColor: color,
      backgroundColor: color + '33',
      tension: 0.25,
      fill: true,
      stack: 'statuses',
    }
  })
  statusConfig.value = {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { labels: { color: '#e5e7eb' } } },
      scales: {
        x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.2)' } },
        y: { stacked: true, ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.2)' } },
      },
    },
  }
}
function next(){ if(page.value * perPage.value < total.value){ page.value++; fetchItems() } }
function prev(){ if(page.value>1){ page.value--; fetchItems() } }

const displayCode = displayOrderCode

onMounted(fetchItems)
onMounted(loadTimeseries)
onMounted(loadStatusSeries)
</script>
