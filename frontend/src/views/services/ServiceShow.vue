<template>
  <div class="card" v-if="item">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">Serviço: {{ item.name }}</h2>
      <div style="display:flex;gap:8px">
        <RouterLink class="button" :to="`/services/${item.id}/edit`">Editar</RouterLink>
        <button v-if="!item.deleted_at" class="button" style="background:#dc2626" @click="destroy">Excluir</button>
        <button v-else class="button" @click="restore">Restaurar</button>
        <RouterLink class="button" style="background:#334155" to="/services">Voltar</RouterLink>
      </div>
    </div>
    <div style="height:12px" />
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:800px">
      <div><strong>Descrição:</strong> {{ item.description || '-' }}</div>
      <div><strong>Preço:</strong> R$ {{ Number(item.price).toFixed(2) }}</div>
      <div><strong>Criado:</strong> {{ new Date(item.created_at).toLocaleString() }}</div>
      <div><strong>Atualizado:</strong> {{ new Date(item.updated_at).toLocaleString() }}</div>
      <div v-if="item.deleted_at" style="grid-column:1/-1;color:#94a3b8">Excluído em: {{ new Date(item.deleted_at).toLocaleString() }}</div>
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

async function load(){
  const { data } = await api.get(`/api/services/${id}`)
  item.value = data.data
}

async function destroy(){
  if(!confirm('Confirma excluir?')) return
  await api.delete(`/api/services/${id}`)
  router.push('/services')
}

async function restore(){
  await api.post(`/api/services/${id}/restore`)
  load()
}

onMounted(load)
</script>
