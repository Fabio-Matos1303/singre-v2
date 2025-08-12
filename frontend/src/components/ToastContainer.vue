<template>
  <div class="toast-wrap">
    <div v-for="t in toasts" :key="t.id" class="toast" :class="t.kind" @click="remove(t.id)">
      {{ t.message }}
    </div>
  </div>
</template>
<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useToastStore } from '../stores/toast'
const toast = useToastStore()
const { toasts } = storeToRefs(toast)
const remove = (id: number) => toast.remove(id)
</script>
<style scoped>
.toast-wrap{position:fixed;right:16px;bottom:16px;display:flex;flex-direction:column;gap:8px;z-index:9999}
.toast{padding:10px 14px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,0.35);cursor:pointer;min-width:220px}
.toast.info{background:#334155;color:#e5e7eb}
.toast.success{background:#16a34a;color:#ecfdf5}
.toast.error{background:#dc2626;color:#fee2e2}
</style>
