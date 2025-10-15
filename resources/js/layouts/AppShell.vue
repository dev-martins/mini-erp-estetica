<template>
  <div class="main-wrapper d-flex flex-column bg-body-tertiary min-vh-100">
    <TopBar
      :user="auth.user"
      @toggle-sidebar="toggleSidebar"
      @logout="handleLogout"
    />
    <div class="flex-grow-1 d-flex position-relative">
      <AppSidebar
        class="d-none d-md-flex"
        :items="navItems"
        :collapsed="sidebarCollapsed"
      />
      <main class="flex-grow-1 main-content container-fluid py-3 px-3 px-md-4">
        <slot />
      </main>
    </div>
    <BottomNav class="d-md-none" :items="navItems" />
    <QuickActionFab class="d-md-none" />
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import TopBar from '../components/navigation/TopBar.vue';
import AppSidebar from '../components/navigation/AppSidebar.vue';
import BottomNav from '../components/navigation/BottomNav.vue';
import QuickActionFab from '../components/navigation/QuickActionFab.vue';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const sidebarCollapsed = ref(false);

const navItems = computed(() => [
  { label: 'Agenda', icon: 'fa-regular fa-calendar-days', name: 'agenda' },
  { label: 'Vendas', icon: 'fa-solid fa-cash-register', name: 'sales' },
  { label: 'Clientes', icon: 'fa-regular fa-user', name: 'clients' },
  { label: 'Estoque', icon: 'fa-solid fa-boxes-stacked', name: 'inventory' },
  { label: 'Relatórios', icon: 'fa-solid fa-chart-line', name: 'reports' },
]);

function toggleSidebar() {
  sidebarCollapsed.value = !sidebarCollapsed.value;
}

async function handleLogout() {
  await auth.logout();
  router.push({ name: 'login' });
}

function handleUnauthorized() {
  auth.clear();
  router.push({ name: 'login', query: { redirect: route.fullPath } });
}

onMounted(() => {
  window.addEventListener('auth:unauthorized', handleUnauthorized);
});

onBeforeUnmount(() => {
  window.removeEventListener('auth:unauthorized', handleUnauthorized);
});
</script>
