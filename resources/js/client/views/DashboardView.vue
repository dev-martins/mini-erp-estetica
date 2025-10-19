<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import http from '../services/http';
import { useClientAuthStore } from '../stores/auth';

const auth = useClientAuthStore();

const loading = ref(false);
const upcoming = ref([]);
const error = ref(null);

const headline = computed(() => upcoming.value[0] ?? null);
const remaining = computed(() => upcoming.value.slice(1));

function formatDateTime(dateTime) {
  if (!dateTime) {
    return '';
  }

  const date = new Date(dateTime);
  return new Intl.DateTimeFormat('pt-BR', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

function formatService(appointment) {
  return appointment?.service?.name ?? 'Sessão agendada';
}

async function fetchUpcoming() {
  loading.value = true;
  error.value = null;

  try {
    const today = new Date().toISOString().split('T')[0];
    const { data } = await http.get('/appointments', {
      params: {
        status: 'pending,confirmed',
        from: today,
        per_page: 5,
      },
    });

    const items = Array.isArray(data.data) ? data.data : [];
    upcoming.value = items;
  } catch (err) {
    console.error(err);
    error.value = 'Não foi possível carregar seus próximos atendimentos.';
    upcoming.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchUpcoming();
});
</script>

<template>
  <div class="d-flex flex-column gap-4">
    <section class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-body p-4">
        <p class="text-body-secondary mb-1">Olá, {{ auth.firstName || auth.client?.full_name }}</p>
        <h2 class="h5 fw-semibold mb-3">Que bom te ver por aqui 👋</h2>
        <p class="text-body-secondary mb-0">
          Acompanhe seus agendamentos, veja os pacotes ativos e atualize seus dados sempre que precisar.
        </p>
      </div>
    </section>

    <section>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h6 fw-semibold mb-0">Próximos atendimentos</h3>
        <RouterLink :to="{ name: 'client.appointments' }" class="text-decoration-none small">
          Ver agenda
        </RouterLink>
      </div>

      <div v-if="loading" class="card border-0 shadow-sm rounded-4">
        <div class="card-body py-5 text-center text-body-secondary">
          Carregando sua agenda...
        </div>
      </div>

      <div v-else-if="error" class="alert alert-warning">
        {{ error }}
      </div>

      <div v-else-if="headline" class="d-flex flex-column gap-3">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4">
            <p class="text-uppercase small fw-semibold text-primary mb-1">Próxima sessão</p>
            <h4 class="h5 fw-semibold mb-1">{{ formatService(headline) }}</h4>
            <p class="text-body-secondary mb-2">
              {{ formatDateTime(headline.scheduled_at) }}
            </p>
            <p class="small text-body-secondary mb-0">
              Profissional: {{ headline.professional?.display_name ?? 'Definido na clínica' }}
            </p>
          </div>
        </div>

        <div v-if="remaining.length" class="d-flex flex-column gap-2">
          <div
            v-for="appointment in remaining"
            :key="appointment.id"
            class="card border-0 shadow-sm rounded-4"
          >
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="fw-semibold mb-1">{{ formatService(appointment) }}</p>
                  <p class="small text-body-secondary mb-0">
                    {{ formatDateTime(appointment.scheduled_at) }}
                  </p>
                </div>
                <span class="badge text-bg-light text-capitalize">
                  {{ appointment.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="card border-0 shadow-sm rounded-4">
        <div class="card-body py-5 text-center text-body-secondary">
          Nenhum atendimento futuro encontrado. Bora agendar?
        </div>
      </div>
    </section>

    <section>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h6 fw-semibold mb-0">Ações rápidas</h3>
      </div>
      <div class="row g-3">
        <div class="col-12 col-sm-4">
          <RouterLink class="card quick-action border-0 shadow-sm h-100" :to="{ name: 'client.appointments' }">
            <div class="card-body d-flex flex-column gap-2">
              <div class="icon bg-primary-subtle text-primary">
                <i class="fa-solid fa-calendar-plus"></i>
              </div>
              <h4 class="h6 fw-semibold mb-0">Agendar sessão</h4>
              <p class="small text-body-secondary mb-0">Escolha o melhor horário disponível no seu pacote.</p>
            </div>
          </RouterLink>
        </div>
        <div class="col-12 col-sm-4">
          <RouterLink class="card quick-action border-0 shadow-sm h-100" :to="{ name: 'client.profile' }">
            <div class="card-body d-flex flex-column gap-2">
              <div class="icon bg-warning-subtle text-warning">
                <i class="fa-solid fa-address-card"></i>
              </div>
              <h4 class="h6 fw-semibold mb-0">Atualizar dados</h4>
              <p class="small text-body-secondary mb-0">Mantenha seu contato e preferências sempre atualizados.</p>
            </div>
          </RouterLink>
        </div>
        <div class="col-12 col-sm-4">
          <a class="card quick-action border-0 shadow-sm h-100" href="https://wa.me/" target="_blank" rel="noreferrer">
            <div class="card-body d-flex flex-column gap-2">
              <div class="icon bg-success-subtle text-success">
                <i class="fa-brands fa-whatsapp"></i>
              </div>
              <h4 class="h6 fw-semibold mb-0">Falar com a clínica</h4>
              <p class="small text-body-secondary mb-0">Tire dúvidas ou ajuste algum detalhe do tratamento.</p>
            </div>
          </a>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.quick-action .icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 1.15rem;
}
</style>
