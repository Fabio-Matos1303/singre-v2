<template>
  <RouterView />
  <ToastContainer />
  
</template>

<script setup lang="ts">
import ToastContainer from './components/ToastContainer.vue'
</script>

<style>
:root {
  color-scheme: light dark;
  --color-bg: #0b132b;
  --color-fg: #e0e6f8;
  --color-accent: #4f46e5;
}
html, body, #app { height: 100%; }
body {
  margin: 0;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
  background: var(--color-bg);
  color: var(--color-fg);
}
</style>
