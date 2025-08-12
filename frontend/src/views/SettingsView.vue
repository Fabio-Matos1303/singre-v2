<template>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
      <h2 style="margin:0">Configurações</h2>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <UIButton type="button" :disabled="saving" :loading="saving" @click="saveAll">Salvar tudo</UIButton>
      </div>
    </div>

    <div style="height:16px" />

    <table class="table">
      <thead>
        <tr>
          <th>Chave</th>
          <th>Valor</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, idx) in rows" :key="row.key || idx">
          <td style="min-width:260px">
            <UIInput v-model="row.key" placeholder="ex: company.name" />
          </td>
          <td>
            <UIInput v-model="row.value" placeholder="valor" />
          </td>
          <td style="width:1%">
            <UIButton type="button" variant="secondary" @click="removeRow(idx)">Remover</UIButton>
          </td>
        </tr>
      </tbody>
    </table>
    <div style="margin-top:12px">
      <UIButton type="button" @click="addRow">+ Adicionar</UIButton>
    </div>

    <p v-if="error" style="color:#fca5a5;margin-top:12px">{{ error }}</p>
    <p v-if="success" style="color:#86efac;margin-top:12px">Configurações salvas.</p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../lib/api'
import UIButton from '../components/UIButton.vue'
import UIInput from '../components/UIInput.vue'

interface Row { key: string; value: string }

const rows = ref<Row[]>([])
const saving = ref(false)
const error = ref('')
const success = ref(false)

function addRow(){ rows.value.push({ key: '', value: '' }) }
function removeRow(i: number){ rows.value.splice(i, 1) }

async function load(){
  const { data } = await api.get('/api/settings')
  rows.value = (data || []).map((s: any) => ({ key: s.key, value: s.value ?? '' }))
}

async function saveAll(){
  saving.value = true
  error.value = ''
  success.value = false
  try{
    for (const r of rows.value) {
      if (!r.key) continue
      await api.post('/api/settings', { key: r.key, value: r.value })
    }
    success.value = true
    const { useToastStore } = await import('../stores/toast')
    useToastStore().success('Configurações salvas com sucesso')
  }catch(e:any){
    error.value = e?.response?.data?.message || 'Falha ao salvar'
    const { useToastStore } = await import('../stores/toast')
    useToastStore().error(error.value)
  }finally{
    saving.value = false
  }
}

onMounted(load)
</script>
