<template>
  <button class="btn" :class="[variant, { loading, disabled } ]" :disabled="disabled || loading" v-bind="$attrs">
    <span v-if="loading" class="spinner" />
    <slot />
  </button>
  
</template>
<script setup lang="ts">
withDefaults(defineProps<{ variant?: 'primary'|'secondary'|'danger'; disabled?: boolean; loading?: boolean }>(), { variant: 'primary', disabled: false, loading: false })
</script>
<style scoped>
.btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:10px 14px;cursor:pointer}
.btn.loading{opacity:0.85}
.btn.disabled{cursor:not-allowed}
.primary{background:#4f46e5;color:#fff}
.secondary{background:#334155;color:#fff}
.danger{background:#dc2626;color:#fff}
.spinner{width:14px;height:14px;border-radius:9999px;border:2px solid rgba(255,255,255,0.6);border-top-color:transparent;animation:spin 0.9s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
