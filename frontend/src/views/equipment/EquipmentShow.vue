<template>
  <div class="card" v-if="item">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">Equipamento #{{ item.id }}</h2>
      <div style="display:flex;gap:8px">
        <RouterLink class="button" style="background:#1d4ed8" :to="`/equipment/${id}/edit`">Editar</RouterLink>
        <RouterLink class="button" style="background:#334155" to="/equipment">Voltar</RouterLink>
      </div>
    </div>

    <div style="height:12px" />

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:900px">
      <div><strong>Cliente:</strong> {{ item.client?.name }} (ID: {{ item.client_id }})</div>
      <div><strong>Intervenções:</strong> {{ item.intervention_count }}</div>
      <div><strong>Série (Empresa):</strong> {{ item.serial_company || '-' }}</div>
      <div><strong>Série (Fabricante):</strong> {{ item.serial_manufacturer || '-' }}</div>
      <div><strong>Marca/Modelo:</strong> {{ item.brand || '-' }} {{ item.model || '' }}</div>
      <div style="grid-column:1/-1"><strong>Configuração:</strong><br/>{{ item.configuration || '-' }}</div>
    </div>

    <div style="height:16px" />

    <div class="card" style="padding:16px">
      <h3 style="margin-top:0">Ordens de Serviço relacionadas</h3>
      <table class="table" v-if="orders.length">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Status</th>
            <th>Total</th>
            <th>Abertura</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in orders" :key="o.id" @click="goOrder(o.id)" style="cursor:pointer">
            <td>{{ o.id }}</td>
            <td>{{ o.client?.name }}</td>
            <td>{{ o.status }}</td>
            <td>R$ {{ Number(o.total || 0).toFixed(2) }}</td>
            <td>{{ o.opened_at ? new Date(o.opened_at).toLocaleString() : '-' }}</td>
          </tr>
        </tbody>
      </table>
      <p v-else>Nenhuma OS vinculada.</p>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../lib/api'

const route = useRoute()
const router = useRouter()
const id = Number(route.params.id)
const item = ref<any | null>(null)
const orders = ref<any[]>([])

async function load(){
  const { data } = await api.get(`/api/equipment/${id}`)
  item.value = data
  const { data: os } = await api.get('/api/service-orders', { params: { per_page: 50, q: '', status: '', from: '', to: '', equipment_id: id } })
  orders.value = os.data || []
}

function goOrder(orderId: number){ router.push(`/service-orders/${orderId}`) }

onMounted(load)
</script>
