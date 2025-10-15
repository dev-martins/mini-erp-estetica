<template>
  <div class="position-fixed end-0 bottom-0 pe-3 pb-5">
    <transition name="fade">
      <ul
        v-if="open"
        class="list-unstyled mb-3 d-flex flex-column gap-2 align-items-end"
      >
        <li v-for="action in actions" :key="action.label">
          <button
            class="btn btn-light border-0 shadow-sm px-3 py-2 rounded-pill"
            type="button"
            @click="go(action)"
          >
            <i :class="[action.icon, 'me-2 text-primary']"></i>{{ action.label }}
          </button>
        </li>
      </ul>
    </transition>
    <button class="btn btn-primary rounded-circle shadow-lg" type="button" @click="toggle">
      <i :class="open ? 'fa-solid fa-xmark' : 'fa-solid fa-plus'" class="fs-4"></i>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const open = ref(false);

const actions = [
  { label: 'Novo agendamento', icon: 'fa-regular fa-calendar-plus', name: 'agenda' },
  { label: 'Nova venda', icon: 'fa-solid fa-cart-plus', name: 'sales' },
  { label: 'Novo cliente', icon: 'fa-regular fa-user', name: 'clients' },
];

function toggle() {
  open.value = !open.value;
}

function go(action) {
  open.value = false;
  router.push({ name: action.name });
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
