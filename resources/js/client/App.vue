<script setup>
import { onBeforeUnmount, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useClientAuthStore } from './stores/auth';

const auth = useClientAuthStore();
const router = useRouter();

const handleUnauthorized = () => {
  auth.clear();
  if (router.currentRoute.value.name !== 'client.login') {
    router.push({ name: 'client.login' });
  }
};

onMounted(() => {
  window.addEventListener('client-auth:unauthorized', handleUnauthorized);
});

onBeforeUnmount(() => {
  window.removeEventListener('client-auth:unauthorized', handleUnauthorized);
});
</script>

<template>
  <router-view />
</template>
