<template>
  <div class="card card-mobile p-4 shadow-sm">
    <div class="mb-4 text-center">
      <h1 class="h4 fw-bold mb-1">Bem-vinda de volta 👋</h1>
      <p class="text-body-secondary mb-0">Acesse para acompanhar sua operação em tempo real.</p>
    </div>
    <form class="d-flex flex-column gap-3" @submit.prevent="submit">
      <div>
        <label class="form-label">E-mail</label>
        <input
          v-model="form.email"
          type="email"
          class="form-control form-control-lg"
          placeholder="voce@suaestetica.com"
          required
          autocomplete="email"
        />
      </div>
      <div>
        <label class="form-label">Senha</label>
        <input
          v-model="form.password"
          type="password"
          class="form-control form-control-lg"
          placeholder="••••••••"
          required
          autocomplete="current-password"
        />
      </div>
      <button class="btn btn-primary btn-lg" type="submit" :disabled="auth.loading">
        <span v-if="auth.loading" class="spinner-border spinner-border-sm me-2" role="status" />
        Entrar
      </button>
      <p v-if="auth.error" class="text-danger small mb-0">{{ auth.error }}</p>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const form = reactive({
  email: '',
  password: '',
});

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

async function submit() {
  const ok = await auth.login(form);
  if (ok) {
    const redirect = route.query.redirect ?? { name: 'agenda' };
    router.push(redirect);
  }
}
</script>
