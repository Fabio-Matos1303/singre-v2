<template>
  <div>
    <label v-if="label">{{ label }}</label>
    <select class="input" v-bind="$attrs" :value="modelValue" @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)">
      <slot />
    </select>
    <p v-if="error" style="color:#fca5a5;margin:6px 0 0">{{ error }}</p>
  </div>
</template>
<script setup lang="ts">
defineProps<{ label?: string; modelValue?: string|number; error?: string }>()
defineEmits<{(e:'update:modelValue', v:string):void}>()
</script>
