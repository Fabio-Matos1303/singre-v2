<template>
  <div class="card" v-if="order">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">OS {{ displayCode(order) }} - {{ order.client?.name }}</h2>
      <div style="display:flex;gap:8px">
        <button class="button" style="background:#334155" @click="printPdf">Imprimir PDF</button>
        <RouterLink class="button" style="background:#1d4ed8" :to="`/service-orders/${id}/edit`">Editar</RouterLink>
        <RouterLink class="button" style="background:#334155" to="/service-orders">Voltar</RouterLink>
      </div>
    </div>

    <div style="height:12px" />

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:900px">
      <div>
        <strong>Status:</strong>
        <select class="input" v-model="status" @change="updateStatus" style="max-width:220px">
          <option value="open">Aberta</option>
          <option value="in_progress">Em andamento</option>
          <option value="closed">Fechada</option>
        </select>
      </div>
      <div><strong>Abertura:</strong> {{ order.opened_at ? new Date(order.opened_at).toLocaleString() : '-' }}</div>
      <div style="grid-column:1/-1">
        <strong>Equipamento:</strong>
        <span v-if="order.equipment">{{ equipmentLabel(order.equipment) }}</span>
        <span v-else>-</span>
      </div>
      <div><strong>Total:</strong> R$ {{ Number(order.total || 0).toFixed(2) }}</div>
      <div style="grid-column:1/-1"><strong>Observações:</strong><br/>{{ order.notes || '-' }}</div>
    </div>

    <div style="height:16px" />

    <div class="card" style="padding:16px">
      <h3 style="margin-top:0">Produtos</h3>
      <table class="table" v-if="order.products?.length">
        <thead>
          <tr>
            <th>Produto</th>
            <th>Qtd</th>
            <th>Unitário</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in order.products" :key="p.id">
            <td>{{ p.name }}</td>
            <td>{{ p.pivot.quantity }}</td>
            <td>R$ {{ Number(p.pivot.unit_price).toFixed(2) }}</td>
            <td>R$ {{ Number(p.pivot.total).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
      <p v-else>Nenhum produto.</p>
    </div>

    <div class="card" style="padding:16px; margin-top:12px">
      <h3 style="margin-top:0">Serviços</h3>
      <table class="table" v-if="order.services?.length">
        <thead>
          <tr>
            <th>Serviço</th>
            <th>Qtd</th>
            <th>Unitário</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in order.services" :key="s.id">
            <td>{{ s.name }}</td>
            <td>{{ s.pivot.quantity }}</td>
            <td>R$ {{ Number(s.pivot.unit_price).toFixed(2) }}</td>
            <td>R$ {{ Number(s.pivot.total).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
      <p v-else>Nenhum serviço.</p>
    </div>

    <div class="card" style="padding:16px; margin-top:12px">
      <h3 style="margin-top:0">Histórico de Status</h3>
      <table class="table" v-if="order.status_histories?.length">
        <thead>
          <tr>
            <th>De</th>
            <th>Para</th>
            <th>Quando</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="h in order.status_histories" :key="h.id">
            <td>{{ h.from_status || '-' }}</td>
            <td>{{ h.to_status }}</td>
            <td>{{ new Date(h.changed_at || h.created_at).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
      <p v-else>Nenhum histórico.</p>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../lib/api'
import { displayOrderCode } from '../../lib/format'

const route = useRoute()
const id = Number(route.params.id)
const order = ref<any | null>(null)
const status = ref('open')
function equipmentLabel(e: any){
  const s1 = e.serial_company || '-'
  const s2 = e.serial_manufacturer || '-'
  const b = e.brand || '-'
  const m = e.model || '-'
  return `${s1}/${s2} — ${b} ${m}`
}

async function load(){
  const { data } = await api.get(`/api/service-orders/${id}`)
  order.value = data
  status.value = data.status
}

function printPdf(){
  window.open(`/api/service-orders/${id}/pdf`, '_blank')
}

async function updateStatus(){
  const { data } = await api.put(`/api/service-orders/${id}`, { status: status.value })
  order.value = data
}

const displayCode = displayOrderCode

onMounted(load)
</script>
