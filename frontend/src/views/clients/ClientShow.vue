<template>
  <div class="card" v-if="client">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">Cliente: {{ client.name }}</h2>
      <div style="display:flex;gap:8px">
        <RouterLink class="button" :to="`/clients/${client.id}/edit`">Editar</RouterLink>
        <button v-if="!client.deleted_at" class="button" style="background:#dc2626" @click="destroy">Excluir</button>
        <button v-else class="button" @click="restore">Restaurar</button>
        <RouterLink class="button" style="background:#334155" to="/clients">Voltar</RouterLink>
      </div>
    </div>
    <div style="height:12px" />
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:800px">
      <div><strong>Email:</strong> {{ client.email || '-' }}</div>
      <div><strong>Telefone:</strong> {{ client.phone || '-' }}</div>
      <div><strong>Documento:</strong> {{ client.document || '-' }}</div>
      <div><strong>Endereço:</strong> {{ client.address || '-' }}</div>
      <div><strong>Criado:</strong> {{ new Date(client.created_at).toLocaleString() }}</div>
      <div><strong>Atualizado:</strong> {{ new Date(client.updated_at).toLocaleString() }}</div>
      <div v-if="client.deleted_at" style="grid-column:1/-1;color:#94a3b8">Excluído em: {{ new Date(client.deleted_at).toLocaleString() }}</div>
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
const client = ref<any | null>(null)

async function load(){
  const { data } = await api.get(`/api/clients/${id}`)
  client.value = data.data
}

async function destroy(){
  if (!confirm('Confirma excluir?')) return
  await api.delete(`/api/clients/${id}`)
  router.push('/clients')
}

async function restore(){
  await api.post(`/api/clients/${id}/restore`)
  load()
}

onMounted(load)
</script>
