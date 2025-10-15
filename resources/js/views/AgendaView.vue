<template>
  <section class="container-fluid px-0 px-md-2">
    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <div class="card card-mobile p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h2 class="h5 fw-semibold mb-0">Agenda inteligente</h2>
              <small class="text-body-secondary">Visualize sessões por profissional ou sala.</small>
            </div>
            <button class="btn btn-outline-primary btn-sm d-none d-md-inline-flex" type="button">
              <i class="fa-regular fa-calendar-plus me-2"></i>Novo agendamento
            </button>
          </div>
          <FullCalendar :options="calendarOptions" />
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="card card-mobile p-3 mb-3">
          <h3 class="h6 fw-semibold mb-3">Próximos atendimentos</h3>
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status" />
          </div>
          <template v-else>
            <div
              v-for="appointment in upcoming"
              :key="appointment.id"
              class="d-flex flex-column gap-1 border rounded-3 p-3 mb-2"
            >
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">{{ appointment.client?.full_name }}</span>
                <span class="badge text-bg-primary">{{ appointment.service?.name }}</span>
              </div>
              <small class="text-body-secondary">
                {{ appointment.when }} · {{ appointment.professional?.display_name ?? 'Equipe' }}
              </small>
            </div>
            <p v-if="!upcoming.length" class="text-body-secondary mb-0">Sem sessões futuras programadas.</p>
          </template>
        </div>
        <div class="card card-mobile p-3">
          <h3 class="h6 fw-semibold mb-3">Indicadores do dia</h3>
          <div class="d-flex flex-wrap gap-3">
            <div class="flex-fill">
              <p class="text-body-secondary small mb-1">Ocupação</p>
              <span class="fs-4 fw-bold text-primary">{{ kpis.occupancy }}%</span>
            </div>
            <div class="flex-fill">
              <p class="text-body-secondary small mb-1">No-show</p>
              <span class="fs-4 fw-bold text-danger">{{ kpis.noShow }}%</span>
            </div>
            <div class="flex-fill">
              <p class="text-body-secondary small mb-1">Ticket médio</p>
              <span class="fs-4 fw-bold text-success">R$ {{ kpis.ticket }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import http from '../services/http';

const loading = ref(false);
const appointments = ref([]);

const kpis = ref({
  occupancy: 0,
  noShow: 0,
  ticket: '0,00',
});

const calendarOptions = computed(() => ({
  plugins: [timeGridPlugin, dayGridPlugin, interactionPlugin],
  initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
  locale: 'pt-br',
  height: 'auto',
  headerToolbar: {
    left: 'title',
    right: 'timeGridDay,timeGridWeek,dayGridMonth prev,next',
  },
  events: appointments.value.map((item) => ({
    id: item.id,
    title: `${item.client?.full_name ?? 'Cliente'} · ${item.service?.name ?? ''}`,
    start: item.scheduled_at,
    end:
      item.ended_at ??
      new Date(new Date(item.scheduled_at).getTime() + (item.duration_min ?? 60) * 60000).toISOString(),
  })),
}));

const upcoming = computed(() =>
  appointments.value
    .slice()
    .sort((a, b) => new Date(a.scheduled_at) - new Date(b.scheduled_at))
    .slice(0, 5)
    .map((item) => ({
      ...item,
      when: new Intl.DateTimeFormat('pt-BR', {
        weekday: 'short',
        hour: '2-digit',
        minute: '2-digit',
      }).format(new Date(item.scheduled_at)),
    })),
);

async function fetchData() {
  loading.value = true;
  try {
    const { data } = await http.get('/appointments', { params: { per_page: 100 } });
    appointments.value = data.data ?? [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchData();
});
</script>
