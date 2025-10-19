<script setup>
import { reactive, ref, watch } from 'vue';
import http from '../services/http';
import { useClientAuthStore } from '../stores/auth';

const auth = useClientAuthStore();

const form = reactive({
  full_name: '',
  email: '',
  phone: '',
  password: '',
});

const saving = ref(false);
const success = ref(false);
const errors = ref({});
const pendingChannels = ref([]);

function resetForm(client) {
  form.full_name = client?.full_name ?? '';
  form.email = client?.email ?? '';
  form.phone = client?.phone ?? '';
  form.password = '';
  pendingChannels.value = client?.verification_channels ?? [];
}

watch(
  () => auth.client,
  (client) => {
    resetForm(client);
  },
  { immediate: true },
);

function fieldError(field) {
  return errors.value?.[field]?.[0] ?? null;
}

async function handleSubmit() {
  saving.value = true;
  success.value = false;
  errors.value = {};

  const payload = {
    full_name: form.full_name || undefined,
    email: form.email || null,
    phone: form.phone || null,
  };

  if (form.password) {
    payload.password = form.password;
  }

  try {
    const { data } = await http.put('/profile', payload);
    success.value = true;
    pendingChannels.value = data.pending_channels ?? [];
    await auth.fetchClient();
    resetForm(auth.client);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors ?? {};
    } else {
      errors.value = { general: ['Não foi possível atualizar seus dados. Tente novamente em instantes.'] };
    }
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <div class="d-flex flex-column gap-4">
    <section class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">
        <h2 class="h5 fw-semibold mb-2">Meus dados</h2>
        <p class="text-body-secondary small mb-0">
          Atualize suas informações e mantenha seus contatos verificados para receber notificações.
        </p>
      </div>
    </section>

    <form class="card border-0 shadow-sm rounded-4" @submit.prevent="handleSubmit" novalidate>
      <div class="card-body p-4">
        <div v-if="errors.general" class="alert alert-danger">
          {{ errors.general[0] }}
        </div>

        <div v-if="success" class="alert alert-success">
          Dados atualizados com sucesso.
        </div>

        <div class="mb-3">
          <label for="full_name" class="form-label">Nome completo</label>
          <input
            id="full_name"
            v-model="form.full_name"
            type="text"
            class="form-control form-control-lg"
            placeholder="Seu nome completo"
            required
          />
          <div v-if="fieldError('full_name')" class="invalid-feedback d-block">
            {{ fieldError('full_name') }}
          </div>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">E-mail</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-control form-control-lg"
            placeholder="email@exemplo.com"
          />
          <div v-if="fieldError('email')" class="invalid-feedback d-block">
            {{ fieldError('email') }}
          </div>
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label">Celular</label>
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            class="form-control form-control-lg"
            placeholder="(00) 00000-0000"
          />
          <div v-if="fieldError('phone')" class="invalid-feedback d-block">
            {{ fieldError('phone') }}
          </div>
        </div>

        <div class="mb-4">
          <label for="password" class="form-label">Nova senha</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control form-control-lg"
            placeholder="Deixe em branco para manter a atual"
          />
          <div v-if="fieldError('password')" class="invalid-feedback d-block">
            {{ fieldError('password') }}
          </div>
        </div>

        <button class="btn btn-primary btn-lg w-100" type="submit" :disabled="saving">
          <span v-if="saving" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          Salvar alterações
        </button>
      </div>

      <div v-if="pendingChannels.length" class="card-footer bg-body-tertiary py-3">
        <p class="small text-body-secondary mb-2">Verifique seu contato:</p>
        <ul class="small text-body-secondary mb-0 ps-3">
          <li v-for="channel in pendingChannels" :key="channel">
            {{ channel === 'email' ? 'E-mail pendente de confirmação' : 'Celular pendente de confirmação' }}
          </li>
        </ul>
      </div>
    </form>
  </div>
</template>
