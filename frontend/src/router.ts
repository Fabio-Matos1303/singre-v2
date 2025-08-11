import { createRouter, createWebHistory } from 'vue-router'
import LoginView from './views/LoginView.vue'
import DashboardView from './views/DashboardView.vue'
import AppLayout from './layouts/AppLayout.vue'
import ClientsList from './views/clients/ClientsList.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', component: LoginView },
    {
      path: '/',
      component: AppLayout,
      children: [
        { path: 'dashboard', component: DashboardView },
        { path: 'clients', component: ClientsList },
      ],
    },
  ],
})

export default router
