import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { public: true },
  },
  {
    path: '/',
    redirect: { name: 'agenda' },
    meta: { requiresAuth: true },
  },
  {
    path: '/agenda',
    name: 'agenda',
    component: () => import('../views/AgendaView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/vendas',
    name: 'sales',
    component: () => import('../views/SalesView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/clientes',
    name: 'clients',
    component: () => import('../views/ClientsView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/estoque',
    name: 'inventory',
    component: () => import('../views/InventoryView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/relatorios',
    name: 'reports',
    component: () => import('../views/ReportsView.vue'),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore();

  if (!auth.initialized) {
    await auth.bootstrap();
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } });
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return next({ name: 'agenda' });
  }

  return next();
});

export default router;
