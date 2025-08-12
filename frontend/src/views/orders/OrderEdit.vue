<template>
  <div class="card" v-if="loaded">
    <h2 style="margin-top:0">Editar Ordem de Serviço</h2>

    <form @submit.prevent="submit" style="display:grid;gap:12px;max-width:900px">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label>Cliente</label>
          <div class="input" style="background:transparent;display:flex;align-items:center;min-height:44px">
            <span>{{ selectedClientLabel }}</span>
          </div>
          <p style="color:#94a3b8;margin:6px 0 0">O cliente não pode ser alterado nesta etapa.</p>
        </div>
        <div>
          <UISelect label="Status" v-model="form.status">
            <option value="open">Aberta</option>
            <option value="in_progress">Em andamento</option>
            <option value="closed">Fechada</option>
          </UISelect>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label>Equipamento (opcional)</label>
          <div class="input" style="display:flex;gap:8px;align-items:center;position:relative">
            <UIInput style="flex:1" v-model="equipmentQuery" placeholder="Buscar por série, marca ou modelo" @input="debouncedSearchEquipment" />
            <UIButton type="button" variant="secondary" @click="addFirstEquipmentResult">Selecionar</UIButton>
            <ul v-if="showEquipmentDropdown" style="position:absolute;z-index:10;left:0;right:120px;top:44px;background:#0f172a;border:1px solid rgba(255,255,255,0.12);border-radius:8px;max-height:220px;overflow:auto;padding:6px;margin:0;list-style:none">
              <li v-for="e in equipmentOptions" :key="e.id" @click="selectEquipment(e)" style="padding:8px;border-radius:6px;cursor:pointer">
                {{ equipmentLabel(e) }}
              </li>
              <li v-if="equipmentLoading" style="padding:8px;color:#94a3b8">Buscando…</li>
              <li v-if="!equipmentLoading && equipmentOptions.length===0" style="padding:8px;color:#94a3b8">Nenhum resultado</li>
            </ul>
          </div>
          <p v-if="form.equipment_id" style="margin:6px 0 0;color:#94a3b8">Selecionado: {{ selectedEquipmentLabel }}</p>
        </div>
        <div>
          <label>Observações</label>
          <textarea class="input" v-model="form.notes" rows="3" />
        </div>
      </div>

      <div class="card" style="padding:16px">
        <h3 style="margin-top:0">Produtos</h3>
        <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;position:relative">
          <UIInput style="flex:1" v-model="productQuery" placeholder="Buscar produto por nome ou SKU" @input="debouncedSearchProducts" />
          <UIButton type="button" @click="addFirstProductResult">Adicionar</UIButton>
          <ul v-if="showProductDropdown" style="position:absolute;z-index:10;left:0;right:120px;top:44px;background:#0f172a;border:1px solid rgba(255,255,255,0.12);border-radius:8px;max-height:220px;overflow:auto;padding:6px;margin:0;list-style:none">
            <li v-for="p in productOptions" :key="p.id" @click="addProductByOption(p)" style="padding:8px;border-radius:6px;cursor:pointer">
              {{ p.name }} <span style="color:#94a3b8">(SKU: {{ p.sku || '-' }})</span> — R$ {{ Number(p.price).toFixed(2) }}
            </li>
            <li v-if="productLoading" style="padding:8px;color:#94a3b8">Buscando…</li>
            <li v-if="!productLoading && productOptions.length===0" style="padding:8px;color:#94a3b8">Nenhum resultado</li>
          </ul>
        </div>
        <div v-for="(line, i) in form.products" :key="i" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:center;margin-bottom:8px">
          <div>{{ productIndex[line.id]?.name || 'Produto não selecionado' }}</div>
          <UIInput type="number" min="1" v-model="(line.quantity as any)" />
          <div>Subtotal: R$ {{ subtotalProduct(line).toFixed(2) }}</div>
          <UIButton type="button" variant="secondary" @click="removeProduct(i)">Remover</UIButton>
        </div>
        <UIButton type="button" @click="addProduct">+ Adicionar linha vazia</UIButton>
      </div>

      <div class="card" style="padding:16px">
        <h3 style="margin-top:0">Serviços</h3>
        <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;position:relative">
          <UIInput style="flex:1" v-model="serviceQuery" placeholder="Buscar serviço por nome" @input="debouncedSearchServices" />
          <UIButton type="button" @click="addFirstServiceResult">Adicionar</UIButton>
          <ul v-if="showServiceDropdown" style="position:absolute;z-index:10;left:0;right:120px;top:44px;background:#0f172a;border:1px solid rgba(255,255,255,0.12);border-radius:8px;max-height:220px;overflow:auto;padding:6px;margin:0;list-style:none">
            <li v-for="s in serviceOptions" :key="s.id" @click="addServiceByOption(s)" style="padding:8px;border-radius:6px;cursor:pointer">
              {{ s.name }} — R$ {{ Number(s.price).toFixed(2) }}
            </li>
            <li v-if="serviceLoading" style="padding:8px;color:#94a3b8">Buscando…</li>
            <li v-if="!serviceLoading && serviceOptions.length===0" style="padding:8px;color:#94a3b8">Nenhum resultado</li>
          </ul>
        </div>
        <div v-for="(line, i) in form.services" :key="i" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:center;margin-bottom:8px">
          <div>{{ serviceIndex[line.id]?.name || 'Serviço não selecionado' }}</div>
          <UIInput type="number" min="1" v-model="(line.quantity as any)" />
          <div>Subtotal: R$ {{ subtotalService(line).toFixed(2) }}</div>
          <UIButton type="button" variant="secondary" @click="removeService(i)">Remover</UIButton>
        </div>
        <UIButton type="button" @click="addService">+ Adicionar linha vazia</UIButton>
      </div>

      <div style="display:flex;justify-content:space-between;align-items:center">
        <strong>Total estimado: R$ {{ totalEstimated.toFixed(2) }}</strong>
        <div>
          <UIButton :disabled="loading" :loading="loading">Salvar alterações</UIButton>
          <RouterLink class="button" style="background:#334155" :to="`/service-orders/${id}`">Cancelar</RouterLink>
        </div>
      </div>

      <p v-if="error" style="color:#fca5a5">{{ error }}</p>
      <ul v-if="Object.keys(errors).length" style="color:#fca5a5">
        <li v-for="(msgs, field) in errors" :key="field">
          {{ field }}: {{ (msgs as string[]).join(', ') }}
        </li>
      </ul>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../lib/api'

const route = useRoute()
const router = useRouter()
const id = Number(route.params.id)

const loaded = ref(false)
const clients = ref<any[]>([])
const products = ref<any[]>([])
const services = ref<any[]>([])
const equipmentOptions = ref<any[]>([])
const equipmentLoading = ref(false)
const equipmentQuery = ref('')
const productIndex = reactive<Record<number, any>>({})
const serviceIndex = reactive<Record<number, any>>({})

const form = reactive({
  client_id: 0,
  equipment_id: 0 as number | 0,
  status: 'open',
  notes: '',
  products: [] as Array<{ id: number; quantity: number }>,
  services: [] as Array<{ id: number; quantity: number }>,
})
const showEquipmentDropdown = computed(() => equipmentQuery.value.length > 1)
function equipmentLabel(e: any){
  const s1 = e.serial_company || '-'
  const s2 = e.serial_manufacturer || '-'
  const b = e.brand || '-'
  const m = e.model || '-'
  return `${s1}/${s2} — ${b} ${m}`
}
const selectedEquipmentLabel = computed(() => {
  const e = equipmentOptions.value.find(x => x.id === form.equipment_id)
  return e ? equipmentLabel(e) : `#${form.equipment_id}`
})
async function searchEquipment(){
  equipmentLoading.value = true
  try{
    const { data } = await api.get('/api/equipment/lookup', { params: { serial: equipmentQuery.value, limit: 10, client_id: form.client_id || undefined } })
    equipmentOptions.value = data.data || []
  } finally { equipmentLoading.value = false }
}
const debouncedSearchEquipment = debounce(searchEquipment, 350)
function selectEquipment(e: any){ form.equipment_id = e.id; if(!equipmentOptions.value.find(x=>x.id===e.id)) equipmentOptions.value.push(e); equipmentQuery.value = equipmentLabel(e) }
function addFirstEquipmentResult(){ const e = equipmentOptions.value[0]; if(e) selectEquipment(e) }

const selectedClientLabel = computed(() => {
  const c = clients.value.find(c => c.id === form.client_id)
  return c ? `${c.name} (${c.email})` : `#${form.client_id}`
})

const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})

function addProduct(){ form.products.push({ id: 0, quantity: 1 }) }
function removeProduct(i: number){ form.products.splice(i, 1) }
function addService(){ form.services.push({ id: 0, quantity: 1 }) }
function removeService(i: number){ form.services.splice(i, 1) }

function findProductPrice(id: number){ return Number(products.value.find(p => p.id === id)?.price || 0) }
function findServicePrice(id: number){ return Number(services.value.find(s => s.id === id)?.price || 0) }
function subtotalProduct(line: { id: number; quantity: number }){ return findProductPrice(line.id) * (line.quantity || 0) }
function subtotalService(line: { id: number; quantity: number }){ return findServicePrice(line.id) * (line.quantity || 0) }
const totalEstimated = computed(() => {
  return form.products.reduce((acc, l) => acc + subtotalProduct(l), 0) +
         form.services.reduce((acc, l) => acc + subtotalService(l), 0)
})

function debounce<T extends (...args: any[]) => any>(fn: T, delay = 300){
  let t: any
  return (...args: any[]) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay) }
}

const productQuery = ref('')
const productOptions = ref<any[]>([])
const productLoading = ref(false)
const showProductDropdown = computed(() => productQuery.value.length > 1)

async function searchProducts(){
  productLoading.value = true
  try {
    const { data } = await api.get('/api/products', { params: { q: productQuery.value, per_page: 10 } })
    productOptions.value = data.data
  } finally { productLoading.value = false }
}
const debouncedSearchProducts = debounce(searchProducts, 350)
function addProductByOption(p: any){ form.products.push({ id: p.id, quantity: 1 }); productIndex[p.id] = p }
function addFirstProductResult(){ const p = productOptions.value[0]; if(p) addProductByOption(p) }

const serviceQuery = ref('')
const serviceOptions = ref<any[]>([])
const serviceLoading = ref(false)
const showServiceDropdown = computed(() => serviceQuery.value.length > 1)

async function searchServices(){
  serviceLoading.value = true
  try {
    const { data } = await api.get('/api/services', { params: { q: serviceQuery.value, per_page: 10 } })
    serviceOptions.value = data.data
  } finally { serviceLoading.value = false }
}
const debouncedSearchServices = debounce(searchServices, 350)
function addServiceByOption(s: any){ form.services.push({ id: s.id, quantity: 1 }); serviceIndex[s.id] = s }
function addFirstServiceResult(){ const s = serviceOptions.value[0]; if(s) addServiceByOption(s) }

async function loadRefs(){
  const [clientsRes, productsRes, servicesRes] = await Promise.all([
    api.get('/api/clients', { params: { per_page: 100 } }),
    api.get('/api/products', { params: { per_page: 100 } }),
    api.get('/api/services', { params: { per_page: 100 } }),
  ])
  clients.value = clientsRes.data.data || clientsRes.data
  products.value = productsRes.data.data || productsRes.data
  services.value = servicesRes.data.data || servicesRes.data
  for (const p of products.value) productIndex[p.id] = p
  for (const s of services.value) serviceIndex[s.id] = s
}

async function loadOrder(){
  const { data } = await api.get(`/api/service-orders/${id}`)
  form.client_id = data.client_id
  form.equipment_id = data.equipment_id || 0
  form.status = data.status
  form.notes = data.notes || ''
  form.products = (data.products || []).map((p: any) => ({ id: p.id, quantity: p.pivot?.quantity || 1 }))
  form.services = (data.services || []).map((s: any) => ({ id: s.id, quantity: s.pivot?.quantity || 1 }))
}

async function submit(){
  loading.value = true
  error.value = ''
  errors.value = {}
  try{
    if (!form.products.length && !form.services.length) {
      throw new Error('Adicione ao menos um produto ou serviço')
    }
    const payload = {
      status: form.status,
      notes: form.notes,
      equipment_id: form.equipment_id || undefined,
      products: form.products.filter(l => l.id && l.quantity),
      services: form.services.filter(l => l.id && l.quantity),
    }
    const { data } = await api.put(`/api/service-orders/${id}`, payload)
    const { useToastStore } = await import('../../stores/toast')
    useToastStore().success('OS atualizada com sucesso')
    router.push(`/service-orders/${data.id}`)
  }catch(e:any){
    error.value = e?.response?.data?.message || 'Erro ao salvar alterações'
    errors.value = e?.response?.data?.errors || {}
    const { useToastStore } = await import('../../stores/toast')
    useToastStore().error(error.value)
  }finally{
    loading.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadRefs(), loadOrder()])
  loaded.value = true
})
</script>
