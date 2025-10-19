<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useClientAuthStore } from '../stores/auth';

const form = reactive({
  login: '',
  password: '',
});

const submitting = ref(false);
const feedback = ref(null);

const auth = useClientAuthStore();
const router = useRouter();
const route = useRoute();

async function handleSubmit() {
  submitting.value = true;
  feedback.value = null;

  const success = await auth.login(form);

  submitting.value = false;

  if (success) {
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null;
    router.push(redirect && redirect.startsWith('/') ? redirect : { name: 'client.dashboard' });
  } else {
    feedback.value = auth.error ?? 'Não foi possível entrar. Tente novamente.';
  }
}
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-body-tertiary p-3">
    <div class="w-100" style="max-width: 420px">
      <div class="text-center mb-4">
        <p class="text-uppercase small fw-semibold text-primary mb-1">Área do Cliente</p>
        <h1 class="h4 fw-bold mb-1">Bem-vinda(o) de volta</h1>
        <p class="text-body-secondary small mb-0">Acompanhe seus agendamentos, pacotes e resultados.</p>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <form @submit.prevent="handleSubmit" novalidate>
            <div class="mb-3">
              <label for="login" class="form-label">E-mail ou celular</label>
              <input
                id="login"
                v-model="form.login"
                type="text"
                class="form-control form-control-lg"
                placeholder="seunome@email.com"
                autocomplete="username"
                required
              />
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Senha</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-control form-control-lg"
                placeholder="Digite sua senha"
                autocomplete="current-password"
                required
              />
            </div>

            <div v-if="feedback" class="alert alert-danger py-2">
              {{ feedback }}
            </div>

            <button class="btn btn-primary btn-lg w-100" type="submit" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Entrar
            </button>
          </form>
        </div>
      </div>

      <p class="text-center small text-body-secondary mt-3 mb-0">
        Precisa de ajuda? Fale com a equipe da clínica para receber uma nova senha.
      </p>
    </div>
  </div>
</template>
