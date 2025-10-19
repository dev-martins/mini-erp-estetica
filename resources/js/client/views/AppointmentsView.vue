<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import http from '../services/http';

const filter = ref('upcoming');
const loading = ref(false);
const items = ref([]);
const error = ref(null);

const hasData = computed(() => items.value.length > 0);

function formatDateTime(dateTime) {
  if (!dateTime) {
    return '';
  }

  const date = new Date(dateTime);
  return new Intl.DateTimeFormat('pt-BR', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

function formatStatus(status) {
  const labels = {
    pending: 'Pendente',
    confirmed: 'Confirmado',
    cancelled: 'Cancelado',
    done: 'Concluído',
    no_show: 'Não compareceu',
  };
  return labels[status] ?? status;
}

async function fetchAppointments() {
  loading.value = true;
  error.value = null;

  try {
    const params = new URLSearchParams({
      per_page: '20',
    });

    if (filter.value === 'upcoming') {
      params.set('status', 'pending,confirmed');
      params.set('from', new Date().toISOString().split('T')[0]);
    } else {
      params.set('status', 'done,cancelled,no_show');
      params.set('to', new Date().toISOString().split('T')[0]);
    }

    const { data } = await http.get('/appointments', { params });
    items.value = Array.isArray(data.data) ? data.data : [];
  } catch (err) {
    console.error(err);
    items.value = [];
    error.value = 'Não foi possível recuperar os atendimentos. Tente novamente.';
  } finally {
    loading.value = false;
  }
}

function statusVariant(status) {
  switch (status) {
    case 'confirmed':
      return 'success';
    case 'pending':
      return 'primary';
    case 'cancelled':
      return 'secondary';
    case 'no_show':
      return 'warning';
    default:
      return 'light';
  }
}

onMounted(fetchAppointments);

watch(filter, () => {
  fetchAppointments();
});
</script>

<template>
  <div class="d-flex flex-column gap-3">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-3 d-flex gap-2">
        <button
          type="button"
          class="btn flex-fill"
          :class="filter === 'upcoming' ? 'btn-primary' : 'btn-outline-primary'"
          @click="filter = 'upcoming'"
        >
          Próximos
        </button>
        <button
          type="button"
          class="btn flex-fill"
          :class="filter === 'past' ? 'btn-primary' : 'btn-outline-primary'"
          @click="filter = 'past'"
        >
          Histórico
        </button>
      </div>
    </div>

    <div v-if="loading" class="card border-0 shadow-sm rounded-4">
      <div class="card-body py-5 text-center text-body-secondary">
        Carregando atendimentos...
      </div>
    </div>

    <div v-else-if="error" class="alert alert-warning mb-0">
      {{ error }}
    </div>

    <div v-else-if="hasData" class="d-flex flex-column gap-3">
      <div
        v-for="appointment in items"
        :key="appointment.id"
        class="card border-0 shadow-sm rounded-4"
      >
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <h4 class="h6 fw-semibold mb-1">
                {{ appointment.service?.name ?? 'Sessão agendada' }}
              </h4>
              <p class="small text-body-secondary mb-2">
                {{ formatDateTime(appointment.scheduled_at) }}
              </p>
              <p v-if="appointment.professional" class="small text-body-secondary mb-0">
                Profissional: {{ appointment.professional.display_name }}
              </p>
            </div>
            <span class="badge text-uppercase" :class="`text-bg-${statusVariant(appointment.status)}`">
              {{ formatStatus(appointment.status) }}
            </span>
          </div>
          <p v-if="appointment.notes" class="small text-body-secondary mb-0 mt-3">
            Observações: {{ appointment.notes }}
          </p>
        </div>
      </div>
    </div>

    <div v-else class="card border-0 shadow-sm rounded-4">
      <div class="card-body py-5 text-center text-body-secondary">
        Nenhum atendimento {{ filter === 'upcoming' ? 'futuro' : 'no histórico' }} encontrado.
      </div>
    </div>
  </div>
</template>
