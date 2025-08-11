<template>
  <div class="layout">
    <aside class="sidebar">
      <div class="brand">SINGRE</div>
      <nav>
        <RouterLink to="/dashboard">Dashboard</RouterLink>
        <RouterLink to="/clients">Clientes</RouterLink>
        <RouterLink to="/products">Produtos</RouterLink>
        <RouterLink to="/services">Serviços</RouterLink>
      </nav>
    </aside>
    <main class="content">
      <header class="topbar">
        <div class="spacer" />
        <button class="button" @click="logout">Sair</button>
      </header>
      <section class="page">
        <RouterView />
      </section>
    </main>
  </div>
</template>
<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
const auth = useAuthStore()
const logout = () => auth.logout().then(() => location.href = '/login')
</script>
<style scoped>
.layout{display:flex;min-height:100vh}
.sidebar{width:240px;background:#0f172a;border-right:1px solid rgba(255,255,255,0.08);padding:20px;position:sticky;top:0;height:100vh}
.brand{color:#93c5fd;font-weight:700;font-size:20px;margin-bottom:16px}
nav{display:flex;flex-direction:column;gap:10px}
nav a{color:#cbd5e1;text-decoration:none;padding:8px 10px;border-radius:8px}
nav a.router-link-active{background:#1f2937;color:#fff}
.content{flex:1;display:flex;flex-direction:column}
.topbar{height:56px;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:flex-end;padding:0 16px;background:#0b132b}
.page{padding:24px}
.button{background:#4f46e5;border:none;color:#fff;padding:8px 12px;border-radius:8px}
</style>
