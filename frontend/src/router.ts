import { createRouter, createWebHistory } from 'vue-router'
import LoginView from './views/LoginView.vue'
import RegisterView from './views/RegisterView.vue'
import DashboardView from './views/DashboardView.vue'
import AppLayout from './layouts/AppLayout.vue'
import ClientsList from './views/clients/ClientsList.vue'
import ClientCreate from './views/clients/ClientCreate.vue'
import ClientEdit from './views/clients/ClientEdit.vue'
import ClientShow from './views/clients/ClientShow.vue'
import ProductsList from './views/products/ProductsList.vue'
import ProductCreate from './views/products/ProductCreate.vue'
import ProductEdit from './views/products/ProductEdit.vue'
import ProductShow from './views/products/ProductShow.vue'
import ServicesList from './views/services/ServicesList.vue'
import ServiceCreate from './views/services/ServiceCreate.vue'
import ServiceEdit from './views/services/ServiceEdit.vue'
import ServiceShow from './views/services/ServiceShow.vue'
import OrdersList from './views/orders/OrdersList.vue'
import OrderCreate from './views/orders/OrderCreate.vue'
import OrderShow from './views/orders/OrderShow.vue'
import OrderEdit from './views/orders/OrderEdit.vue'
import ReportsView from './views/ReportsView.vue'
import SettingsView from './views/SettingsView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', component: () => import('./views/LoginView.vue') },
    { path: '/register', component: () => import('./views/RegisterView.vue') },
    {
      path: '/',
      component: AppLayout,
      children: [
        { path: 'dashboard', component: () => import('./views/DashboardView.vue') },
        { path: 'clients', component: () => import('./views/clients/ClientsList.vue') },
        { path: 'clients/new', component: () => import('./views/clients/ClientCreate.vue') },
        { path: 'clients/:id', component: () => import('./views/clients/ClientShow.vue') },
        { path: 'clients/:id/edit', component: () => import('./views/clients/ClientEdit.vue') },
        { path: 'equipment', component: () => import('./views/equipment/EquipmentList.vue') },
        { path: 'equipment/new', component: () => import('./views/equipment/EquipmentCreate.vue') },
        { path: 'equipment/:id', component: () => import('./views/equipment/EquipmentShow.vue') },
        { path: 'equipment/:id/edit', component: () => import('./views/equipment/EquipmentEdit.vue') },
        { path: 'products', component: () => import('./views/products/ProductsList.vue') },
        { path: 'products/new', component: () => import('./views/products/ProductCreate.vue') },
        { path: 'products/:id', component: () => import('./views/products/ProductShow.vue') },
        { path: 'products/:id/edit', component: () => import('./views/products/ProductEdit.vue') },
        { path: 'services', component: () => import('./views/services/ServicesList.vue') },
        { path: 'services/new', component: () => import('./views/services/ServiceCreate.vue') },
        { path: 'services/:id', component: () => import('./views/services/ServiceShow.vue') },
        { path: 'services/:id/edit', component: () => import('./views/services/ServiceEdit.vue') },
        { path: 'service-orders', component: () => import('./views/orders/OrdersList.vue') },
        { path: 'service-orders/new', component: () => import('./views/orders/OrderCreate.vue') },
        { path: 'service-orders/:id', component: () => import('./views/orders/OrderShow.vue') },
        { path: 'service-orders/:id/edit', component: () => import('./views/orders/OrderEdit.vue') },
        { path: 'reports', component: () => import('./views/ReportsView.vue') },
        { path: 'settings', component: () => import('./views/SettingsView.vue') },
      ],
    },
  ],
})

// Simple auth guard
router.beforeEach((to) => {
  const token = localStorage.getItem('auth_token')
  const publicPaths = ['/login', '/register']
  if (!token && !publicPaths.includes(to.path)) return '/login'
  if (token && publicPaths.includes(to.path)) return '/dashboard'
})

export default router
