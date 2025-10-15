<template>
  <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid px-3">
      <button
        class="btn btn-link text-body d-md-none me-2"
        type="button"
        @click="$emit('toggle-sidebar')"
      >
        <i class="fa-solid fa-bars"></i>
      </button>
      <span class="navbar-brand mb-0 h1">Estética<span class="text-primary">ERP</span></span>
      <div class="ms-auto d-flex align-items-center gap-3">
        <div v-if="user" class="text-end d-none d-md-block">
          <span class="d-block fw-semibold">{{ user.name }}</span>
          <small class="text-body-secondary text-uppercase">{{ user.role }}</small>
        </div>
        <button class="btn btn-outline-secondary btn-sm" type="button" @click="$emit('logout')">
          <i class="fa-solid fa-arrow-right-from-bracket me-1"></i>
          Sair
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
defineProps({
  user: {
    type: Object,
    default: null,
  },
});
</script>
