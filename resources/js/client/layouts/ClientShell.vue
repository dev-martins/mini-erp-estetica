<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useClientAuthStore } from '../stores/auth';

const auth = useClientAuthStore();
const route = useRoute();
const router = useRouter();

const navItems = [
  { name: 'client.dashboard', label: 'Início', icon: 'fa-solid fa-house' },
  { name: 'client.appointments', label: 'Agenda', icon: 'fa-solid fa-calendar-check' },
  { name: 'client.profile', label: 'Perfil', icon: 'fa-solid fa-user' },
];

const activeRoute = computed(() => route.name);

async function handleLogout() {
  await auth.logout();
  router.push({ name: 'client.login' });
}
</script>

<template>
  <div class="client-shell d-flex flex-column min-vh-100 bg-body-tertiary">
    <header class="bg-primary text-white py-3 shadow-sm">
      <div class="container d-flex justify-content-between align-items-center">
        <div>
          <p class="mb-0 text-uppercase small fw-semibold opacity-75">Área do Cliente</p>
          <h1 class="h5 fw-semibold mb-0">{{ auth.firstName || auth.client?.full_name }}</h1>
        </div>
        <button class="btn btn-outline-light btn-sm" type="button" @click="handleLogout">
          Sair
        </button>
      </div>
    </header>

    <main class="flex-grow-1 container py-4">
      <router-view />
    </main>

    <nav class="client-nav border-top bg-white shadow-sm">
      <div class="container d-flex justify-content-around py-2">
        <router-link
          v-for="item in navItems"
          :key="item.name"
          :to="{ name: item.name }"
          class="nav-link text-center flex-grow-1"
          :class="{ active: activeRoute === item.name }"
        >
          <div class="d-flex flex-column align-items-center gap-1">
            <i :class="[item.icon, 'fs-6']"></i>
            <span class="small">{{ item.label }}</span>
          </div>
        </router-link>
      </div>
    </nav>
  </div>
</template>

<style scoped>
.client-shell {
  padding-bottom: 70px;
}

.client-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
}

@media (min-width: 768px) {
  .client-shell {
    padding-bottom: 0;
  }

  .client-nav {
    position: static;
  }

  .client-nav .nav-link {
    padding: 0.75rem 1.5rem;
  }
}

.nav-link {
  color: var(--bs-gray-700);
  text-decoration: none;
}

.nav-link.active {
  color: var(--bs-primary);
  font-weight: 600;
}
</style>
