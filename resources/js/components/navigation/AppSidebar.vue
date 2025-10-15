<template>
  <aside class="bg-white border-end" style="width: 240px;">
    <nav class="nav flex-column py-4 px-3 gap-2">
      <RouterLink
        v-for="item in items"
        :key="item.name"
        :to="{ name: item.name }"
        class="nav-link d-flex align-items-center gap-3 rounded-pill px-3 py-2"
        :class="isActive(item.name) ? 'bg-primary text-white shadow-sm' : 'text-body-secondary'"
      >
        <i :class="[item.icon, 'fs-5']"></i>
        <span class="fw-medium">{{ item.label }}</span>
      </RouterLink>
    </nav>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router';

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  collapsed: {
    type: Boolean,
    default: false,
  },
});

const route = useRoute();

function isActive(name) {
  return route.name === name;
}
</script>
