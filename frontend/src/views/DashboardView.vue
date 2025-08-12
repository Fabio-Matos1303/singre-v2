<template>
  <div class="container">
    <div class="card" style="margin-bottom:16px">
      <h2 style="margin-top:0">Dashboard</h2>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
        <div>
          <label>De</label>
          <input class="input" type="date" v-model="from" />
        </div>
        <div>
          <label>Até</label>
          <input class="input" type="date" v-model="to" />
        </div>
        <button class="button" @click="loadAll">Atualizar</button>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
      <div class="card">
        <div style="color:#94a3b8">Pedidos</div>
        <div style="font-size:28px;font-weight:600">{{ kpis.orders_count }}</div>
      </div>
      <div class="card">
        <div style="color:#94a3b8">Receita</div>
        <div style="font-size:28px;font-weight:600">R$ {{ kpis.revenue_total.toFixed(2) }}</div>
      </div>
      <div class="card">
        <div style="color:#94a3b8">Ticket médio</div>
        <div style="font-size:28px;font-weight:600">R$ {{ kpis.avg_order_value.toFixed(2) }}</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="card">
        <h3 style="margin-top:0">Top Clientes</h3>
        <table class="table">
          <thead><tr><th>Cliente</th><th>Total</th></tr></thead>
          <tbody>
            <tr v-for="c in topClients" :key="c.client_id">
              <td>{{ clientName(c.client_id) }}</td>
              <td>R$ {{ Number(c.total||0).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card">
        <h3 style="margin-top:0">Top Produtos</h3>
        <table class="table">
          <thead><tr><th>Produto</th><th>Qtd</th><th>Total</th></tr></thead>
          <tbody>
            <tr v-for="p in topProducts" :key="p.product_id">
              <td>{{ productName(p.product_id) }}</td>
              <td>{{ p.quantity }}</td>
              <td>R$ {{ Number(p.total||0).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card">
        <h3 style="margin-top:0">Top Serviços</h3>
        <table class="table">
          <thead><tr><th>Serviço</th><th>Qtd</th><th>Total</th></tr></thead>
          <tbody>
            <tr v-for="s in topServices" :key="s.service_id">
              <td>{{ serviceName(s.service_id) }}</td>
              <td>{{ s.quantity }}</td>
              <td>R$ {{ Number(s.total||0).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card">
        <h3 style="margin-top:0">Resumo por Status</h3>
        <table class="table">
          <thead><tr><th>Status</th><th>Qtd</th><th>Total</th></tr></thead>
          <tbody>
            <tr v-for="s in summary.by_status" :key="s.status">
              <td>{{ s.status }}</td>
              <td>{{ s.count }}</td>
              <td>R$ {{ Number(s.total||0).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../lib/api'

const from = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10))
const to = ref(new Date().toISOString().slice(0,10))

const kpis = ref({ orders_count: 0, revenue_total: 0, avg_order_value: 0 })
const summary = ref<any>({ by_status: [] })
const topClients = ref<any[]>([])
const topProducts = ref<any[]>([])
const topServices = ref<any[]>([])
const clientsCache = new Map<number, string>()
const productsCache = new Map<number, string>()
const servicesCache = new Map<number, string>()

function clientName(id: number){ return clientsCache.get(id) || `#${id}` }
function productName(id: number){ return productsCache.get(id) || `#${id}` }
function serviceName(id: number){ return servicesCache.get(id) || `#${id}` }

async function loadAll(){
  const params = { from: from.value, to: to.value }
  try{
    const [kpisRes, summaryRes, topRes] = await Promise.all([
      api.get('/api/reports/kpis', { params }),
      api.get('/api/reports/summary', { params }),
      api.get('/api/reports/top', { params }),
    ])
    kpis.value = kpisRes.data
    summary.value = summaryRes.data
    topClients.value = topRes.data.top_clients
    topProducts.value = topRes.data.top_products
    topServices.value = topRes.data.top_services
  } catch (e:any) {
    const { useToastStore } = await import('../stores/toast')
    useToastStore().error(e?.response?.data?.message || 'Falha ao carregar dashboard')
    return
  }

  // hydrate names caches (best effort)
  const clientIds = topClients.value.map((c:any)=>c.client_id)
  const productIds = topProducts.value.map((p:any)=>p.product_id)
  const serviceIds = topServices.value.map((s:any)=>s.service_id)
  if (clientIds.length) {
    const { data } = await api.get('/api/clients', { params: { per_page: 100 } })
    for (const c of data.data) clientsCache.set(c.id, c.name)
  }
  if (productIds.length) {
    const { data } = await api.get('/api/products', { params: { per_page: 100 } })
    for (const p of data.data) productsCache.set(p.id, p.name)
  }
  if (serviceIds.length) {
    const { data } = await api.get('/api/services', { params: { per_page: 100 } })
    for (const s of data.data) servicesCache.set(s.id, s.name)
  }
}

onMounted(loadAll)
</script>
