<template>
  <nav class="position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-sm py-2 px-3 z-3">
    <div class="d-flex justify-content-between">
      <RouterLink
        v-for="item in items"
        :key="item.name"
        :to="{ name: item.name }"
        class="flex-fill text-center text-decoration-none text-body-secondary"
        :class="{ 'text-primary fw-semibold': isActive(item.name) }"
      >
        <div class="d-flex flex-column align-items-center gap-1">
          <i :class="[item.icon, 'fs-5']"></i>
          <small>{{ item.label }}</small>
        </div>
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router';

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
});

const route = useRoute();

function isActive(name) {
  return route.name === name;
}
</script>
