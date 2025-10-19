import { createRouter, createWebHistory } from 'vue-router';
import { useClientAuthStore } from '../stores/auth';

const router = createRouter({
  history: createWebHistory('/cliente'),
  routes: [
    {
      path: '/login',
      name: 'client.login',
      component: () => import('../views/LoginView.vue'),
      meta: { public: true },
    },
    {
      path: '/',
      component: () => import('../layouts/ClientShell.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'client.dashboard',
          component: () => import('../views/DashboardView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'agendamentos',
          name: 'client.appointments',
          component: () => import('../views/AppointmentsView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'perfil',
          name: 'client.profile',
          component: () => import('../views/ProfileView.vue'),
          meta: { requiresAuth: true },
        },
      ],
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: { name: 'client.dashboard' },
    },
  ],
  scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach(async (to, from, next) => {
  const auth = useClientAuthStore();

  if (!auth.initialized) {
    await auth.bootstrap();
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({
      name: 'client.login',
      query: { redirect: to.fullPath },
    });
  }

  if (to.name === 'client.login' && auth.isAuthenticated) {
    const redirect = from.query?.redirect ?? to.query?.redirect;
    if (typeof redirect === 'string' && redirect.startsWith('/')) {
      return next(redirect);
    }

    return next({ name: 'client.dashboard' });
  }

  return next();
});

export default router;
