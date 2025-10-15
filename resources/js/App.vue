<template>
  <component :is="layoutComponent">
    <router-view />
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppShell from './layouts/AppShell.vue';
import AuthLayout from './layouts/AuthLayout.vue';

const route = useRoute();

const layoutComponent = computed(() => (route.meta?.public ? AuthLayout : AppShell));
</script>
