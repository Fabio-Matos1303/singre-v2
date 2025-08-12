<template>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
      <h2 style="margin:0">Serviços</h2>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input class="input" style="width:240px" v-model="q" placeholder="Buscar por nome" @keyup.enter="fetchItems" />
        <select class="input" style="width:160px" v-model="trashed">
          <option value="">Ativos</option>
          <option value="with">Com excluídos</option>
          <option value="only">Apenas excluídos</option>
        </select>
        <select class="input" style="width:200px" v-model="sort">
          <option value="created_at">Criado em</option>
          <option value="name">Nome</option>
          <option value="price">Preço</option>
        </select>
        <select class="input" style="width:140px" v-model="order">
          <option value="desc">Desc</option>
          <option value="asc">Asc</option>
        </select>
        <button class="button" @click="fetchItems">Buscar</button>
        <RouterLink class="button" to="/services/new">Novo</RouterLink>
      </div>
    </div>
    <div style="height:16px" />
    <table class="table">
      <thead>
        <tr>
          <th @click="toggleSort('name')" style="cursor:pointer">Nome <span style="opacity:.7">{{ sortIcon('name') }}</span></th>
          <th @click="toggleSort('price')" style="cursor:pointer">Preço <span style="opacity:.7">{{ sortIcon('price') }}</span></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="s in items" :key="s.id" @click="goShow(s.id)" style="cursor:pointer">
          <td>
            <span>{{ s.name }}</span>
            <span v-if="s.deleted_at" style="margin-left:8px;color:#94a3b8">(excluído)</span>
          </td>
          <td>R$ {{ Number(s.price).toFixed(2) }}</td>
          <td style="text-align:right">
            <button class="button" v-if="s.deleted_at" @click.stop="restore(s.id)">Restaurar</button>
            <RouterLink class="button" :to="`/services/${s.id}/edit`" @click.stop>Editar</RouterLink>
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
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../lib/api'

const items = ref<any[]>([])
const q = ref('')
const trashed = ref('')
const page = ref(1)
const perPage = ref(15)
const total = ref(0)
const router = useRouter()
const sort = ref('created_at')
const order = ref<'asc'|'desc'>('desc')

async function fetchItems(){
  const params: Record<string, any> = { q: q.value, page: page.value, sort: sort.value, order: order.value }
  if (trashed.value === 'with') params.with_trashed = true
  if (trashed.value === 'only') params.only_trashed = true
  const { data } = await api.get('/api/services', { params })
  items.value = data.data
  total.value = data.total
}
function next(){ if(page.value * perPage.value < total.value){ page.value++; fetchItems() } }
function prev(){ if(page.value>1){ page.value--; fetchItems() } }
async function restore(id: number){ await api.post(`/api/services/${id}/restore`); fetchItems() }
function goShow(id: number){ router.push(`/services/${id}`) }
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

onMounted(fetchItems)
watch([sort, order], () => {
  localStorage.setItem('services.sort', sort.value)
  localStorage.setItem('services.order', order.value)
})
onMounted(() => {
  const s = localStorage.getItem('services.sort')
  const o = localStorage.getItem('services.order') as 'asc'|'desc'|null
  if (s) sort.value = s
  if (o === 'asc' || o === 'desc') order.value = o
})
</script>
