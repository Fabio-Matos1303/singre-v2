<template>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
      <h2 style="margin:0">Clientes</h2>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input class="input" style="width:240px" v-model="q" placeholder="Buscar por nome/email" @keyup.enter="fetchClients" />
        <select class="input" style="width:160px" v-model="trashed">
          <option value="">Ativos</option>
          <option value="with">Com excluídos</option>
          <option value="only">Apenas excluídos</option>
        </select>
        <button class="button" @click="fetchClients">Buscar</button>
      </div>
    </div>
    <div style="height:16px" />
    <table class="table">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Email</th>
          <th>Criado em</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in clients" :key="c.id">
          <td>{{ c.name }}</td>
          <td>{{ c.email }}</td>
          <td>{{ new Date(c.created_at).toLocaleString() }}</td>
          <td style="text-align:right">
            <button class="button" v-if="c.deleted_at" @click="restore(c.id)">Restaurar</button>
          </td>
        </tr>
      </tbody>
    </table>
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
      <button class="button" :disabled="page===1" @click="prev">Anterior</button>
      <button class="button" :disabled="clients.length<perPage" @click="next">Próxima</button>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../lib/api'

const clients = ref<any[]>([])
const q = ref('')
const trashed = ref('') // '' | 'with' | 'only'
const page = ref(1)
const perPage = 15

async function fetchClients() {
  const params: Record<string, any> = { q: q.value, page: page.value }
  if (trashed.value === 'with') params.with_trashed = true
  if (trashed.value === 'only') params.only_trashed = true
  const { data } = await api.get('/api/clients', { params })
  clients.value = data.data
}
function next(){ page.value++; fetchClients() }
function prev(){ if(page.value>1){ page.value--; fetchClients() } }
async function restore(id: number){ await api.post(`/api/clients/${id}/restore`); fetchClients() }

onMounted(fetchClients)
</script>
