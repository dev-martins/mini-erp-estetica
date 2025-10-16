<template>
  <section class="container-fluid px-0 px-md-2">
    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <div class="card card-mobile p-3">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
              <h2 class="h5 fw-semibold mb-0">Agenda inteligente</h2>
              <small class="text-body-secondary">Visualize sessoes por profissional ou sala.</small>
            </div>
            <button
              class="btn btn-outline-primary btn-sm d-none d-md-inline-flex"
              type="button"
              @click="showNewAppointment = true"
            >
              <i class="fa-regular fa-calendar-plus me-2"></i>
              Novo agendamento
            </button>
          </div>
          <FullCalendar :options="calendarOptions" />
        </div>
        <button
          class="btn btn-primary w-100 d-md-none mt-3"
          type="button"
          @click="showNewAppointment = true"
        >
          <i class="fa-regular fa-calendar-plus me-2"></i>
          Novo agendamento
        </button>
      </div>

      <div class="col-12 col-lg-4">
        <div class="card card-mobile p-3 mb-3">
          <h3 class="h6 fw-semibold mb-3">Proximos atendimentos</h3>
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
                <span class="fw-semibold">{{ appointment.client?.full_name ?? 'Cliente' }}</span>
                <span class="badge text-bg-primary">{{ appointment.service?.name ?? 'Servico' }}</span>
              </div>
              <small class="text-body-secondary">
                {{ appointment.when }} - {{ appointment.professional?.display_name ?? 'Equipe' }}
              </small>
            </div>
            <p v-if="!upcoming.length" class="text-body-secondary mb-0">
              Sem sessoes futuras programadas.
            </p>
          </template>
        </div>

        <div class="card card-mobile p-3">
          <h3 class="h6 fw-semibold mb-3">Indicadores do dia</h3>
          <div class="d-flex flex-wrap gap-3">
            <div class="flex-fill">
              <p class="text-body-secondary small mb-1">Ocupacao</p>
              <span class="fs-4 fw-bold text-primary">{{ kpis.occupancy }}%</span>
            </div>
            <div class="flex-fill">
              <p class="text-body-secondary small mb-1">No-show</p>
              <span class="fs-4 fw-bold text-danger">{{ kpis.noShow }}%</span>
            </div>
            <div class="flex-fill">
              <p class="text-body-secondary small mb-1">Ticket medio</p>
              <span class="fs-4 fw-bold text-success">R$ {{ kpis.ticket }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <NewAppointmentModal
      :show="showNewAppointment"
      :existing-appointments="appointments"
      @close="showNewAppointment = false"
      @created="onAppointmentCreated"
    />
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import NewAppointmentModal from '../components/agenda/NewAppointmentModal.vue';
import http from '../services/http';

const loading = ref(false);
const appointments = ref([]);
const showNewAppointment = ref(false);

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
    title: `${item.client?.full_name ?? 'Cliente'} - ${item.service?.name ?? ''}`,
    start: item.scheduled_at,
    end:
      item.ended_at ??
      new Date(new Date(item.scheduled_at).getTime() + (item.duration_min ?? 60) * 60000).toISOString(),
  })),
}));

const UPCOMING_ACCEPTED_STATUSES = ['pending', 'confirmed', 'rescheduled'];
const upcomingFormatter = new Intl.DateTimeFormat('pt-BR', {
  weekday: 'short',
  day: '2-digit',
  month: '2-digit',
  hour: '2-digit',
  minute: '2-digit',
});

const upcoming = computed(() => {
  const now = new Date();

  return appointments.value
    .map((item) => ({
      ...item,
      scheduledDate: item.scheduled_at ? new Date(item.scheduled_at) : null,
      statusKey: String(item.status ?? '').toLowerCase(),
    }))
    .filter(
      (item) =>
        item.scheduledDate instanceof Date &&
        !Number.isNaN(item.scheduledDate?.getTime?.()) &&
        item.scheduledDate >= now &&
        UPCOMING_ACCEPTED_STATUSES.includes(item.statusKey),
    )
    .sort((a, b) => a.scheduledDate - b.scheduledDate)
    .slice(0, 5)
    .map((item) => ({
      ...item,
      when: upcomingFormatter.format(item.scheduledDate),
    }));
});

async function fetchData() {
  loading.value = true;
  try {
    const { data } = await http.get('/appointments', { params: { per_page: 100 } });
    appointments.value = data.data ?? [];
  } finally {
    loading.value = false;
  }
}

async function onAppointmentCreated() {
  showNewAppointment.value = false;
  await fetchData();
}

onMounted(() => {
  fetchData();
});
</script>
