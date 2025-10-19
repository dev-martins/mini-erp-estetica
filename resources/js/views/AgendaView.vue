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
          <div class="d-flex flex-column gap-2 gap-sm-0 flex-sm-row justify-content-between align-items-sm-center mb-3">
            <div>
              <h3 class="h6 fw-semibold mb-0">{{ upcomingTitle }}</h3>
              <small v-if="filterHelperText" class="text-body-secondary">{{ filterHelperText }}</small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <select
                v-model="filterType"
                class="form-select form-select-sm w-auto"
                aria-label="Filtro de periodo"
              >
                <option value="">Proximos</option>
                <option value="month">Mes</option>
                <option value="week">Semana</option>
                <option value="day">Dia</option>
              </select>
              <input
                v-if="filterType === 'month'"
                v-model="filterMonth"
                type="month"
                class="form-control form-control-sm w-auto"
                aria-label="Selecionar mes"
              />
              <input
                v-else-if="filterType === 'week' || filterType === 'day'"
                v-model="filterDate"
                type="date"
                class="form-control form-control-sm w-auto"
                aria-label="Selecionar dia"
              />
              <button
                v-if="filterType"
                type="button"
                class="btn btn-link btn-sm text-decoration-none px-1"
                @click="clearFilters"
              >
                Limpar
              </button>
            </div>
          </div>
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status" />
          </div>
          <template v-else>
            <p v-if="statusUpdateError" class="text-danger small mb-3">{{ statusUpdateError }}</p>
            <div
              v-for="appointment in upcoming"
              :key="appointment.id"
              class="d-flex flex-column gap-2 border rounded-3 p-3 mb-2"
            >
              <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div class="d-flex flex-column">
                  <span class="fw-semibold">{{ appointment.client?.full_name ?? 'Cliente' }}</span>
                  <small class="text-body-secondary">
                    {{ appointment.when }} - {{ appointment.professional?.display_name ?? 'Equipe' }}
                  </small>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                  <span class="badge" :class="`text-bg-${getStatusVariant(appointment.status)}`">
                    {{ getStatusLabel(appointment.status) }}
                  </span>
                  <span class="badge text-bg-primary">{{ appointment.service?.name ?? 'Servico' }}</span>
                  <small
                    v-if="appointment.sessionProgress"
                    class="text-body-secondary fw-semibold text-end"
                  >
                    {{ appointment.sessionProgress }}
                  </small>
                </div>
              </div>
              <div v-if="appointment.isPast" class="d-flex flex-wrap gap-2 mt-1">
                <button
                  class="btn btn-outline-success btn-sm"
                  type="button"
                  :disabled="updatingStatusId === appointment.id || appointment.status === 'completed'"
                  @click="markAttendance(appointment.id, 'completed')"
                >
                  <span
                    v-if="updatingStatusId === appointment.id && appointment.status !== 'completed'"
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                  />
                  Compareceu
                </button>
                <button
                  class="btn btn-outline-danger btn-sm"
                  type="button"
                  :disabled="updatingStatusId === appointment.id || appointment.status === 'no_show'"
                  @click="markAttendance(appointment.id, 'no_show')"
                >
                  <span
                    v-if="updatingStatusId === appointment.id && appointment.status !== 'no_show'"
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                  />
                  No-show
                </button>
              </div>
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
              <span class="fs-4 fw-bold text-primary">{{ formatPercent(kpis.occupancy) }}%</span>
            </div>
            <div class="flex-fill">
              <p class="text-body-secondary small mb-1">No-show</p>
              <span class="fs-4 fw-bold text-danger">{{ formatPercent(kpis.noShow) }}%</span>
            </div>
            <div class="flex-fill">
              <p class="text-body-secondary small mb-1">Ticket medio</p>
              <span class="fs-4 fw-bold text-success">R$ {{ formatCurrency(kpis.ticket) }}</span>
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
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import NewAppointmentModal from '../components/agenda/NewAppointmentModal.vue';
import http from '../services/http';

const loading = ref(false);
const appointments = ref([]);
const showNewAppointment = ref(false);
const updatingStatusId = ref(null);
const statusUpdateError = ref('');

const kpis = ref({
  occupancy: 0,
  noShow: 0,
  ticket: 0,
});

const initialTodayIso = todayIso();
const filterType = ref('');
const filterMonth = ref(initialTodayIso.slice(0, 7));
const filterDate = ref(initialTodayIso);

const STATUS_LABELS = {
  pending: 'Pendente',
  confirmed: 'Confirmado',
  completed: 'Concluido',
  no_show: 'No-show',
  cancelled: 'Cancelado',
};

const STATUS_VARIANTS = {
  pending: 'warning',
  confirmed: 'primary',
  completed: 'success',
  no_show: 'danger',
  cancelled: 'secondary',
};

const UPCOMING_ACCEPTED_STATUSES = ['pending', 'confirmed', 'rescheduled'];
const KPI_ACCEPTED_STATUSES = new Set(['pending', 'confirmed', 'completed', 'no_show']);

watch(filterType, (type) => {
  if (!type) {
    return;
  }

  if (type === 'month' && !filterMonth.value) {
    filterMonth.value = todayIso().slice(0, 7);
  }

  if ((type === 'week' || type === 'day') && !filterDate.value) {
    filterDate.value = todayIso();
  }
});

const filterRange = computed(() => {
  const type = filterType.value;
  if (!type) {
    return null;
  }

  if (type === 'month') {
    const [yearRaw, monthRaw] = String(filterMonth.value ?? '').split('-');
    const year = Number(yearRaw);
    const month = Number(monthRaw);

    if (!year || !month) {
      return null;
    }

    const start = new Date(year, month - 1, 1);
    const end = new Date(year, month, 0);
    end.setHours(23, 59, 59, 999);

    return { type, start, end };
  }

  const [yearRaw, monthRaw, dayRaw] = String(filterDate.value ?? '').split('-');
  const year = Number(yearRaw);
  const month = Number(monthRaw);
  const day = Number(dayRaw);

  if (!year || !month || !day) {
    return null;
  }

  const base = new Date(year, month - 1, day);
  base.setHours(0, 0, 0, 0);

  if (type === 'day') {
    const start = new Date(base);
    const end = new Date(base);
    end.setHours(23, 59, 59, 999);
    return { type, start, end };
  }

  if (type === 'week') {
    const start = new Date(base);
    const dayOfWeek = start.getDay();
    const distanceToMonday = (dayOfWeek + 6) % 7;
    start.setDate(start.getDate() - distanceToMonday);
    start.setHours(0, 0, 0, 0);

    const end = new Date(start);
    end.setDate(start.getDate() + 6);
    end.setHours(23, 59, 59, 999);

    return { type, start, end };
  }

  return null;
});

const upcomingTitle = computed(() => {
  const type = filterType.value;
  const range = filterRange.value;

  if (!type || !range) {
    return 'Proximos atendimentos';
  }

  if (type === 'month') {
    const formatter = new Intl.DateTimeFormat('pt-BR', { month: 'long' });
    const monthLabel = formatter.format(range.start);
    return `Agendamentos de ${capitalize(monthLabel)}`;
  }

  if (type === 'week') {
    return `Agendamentos do dia ${formatDayMonth(range.start)} a ${formatDayMonth(range.end)}`;
  }

  if (type === 'day') {
    return `Agendamentos do dia ${formatDayMonth(range.start)}`;
  }

  return 'Agendamentos filtrados';
});

const filterHelperText = computed(() => {
  const type = filterType.value;
  if (!type) {
    return '';
  }

  if (!filterRange.value) {
    return 'Selecione um periodo valido.';
  }

  if (type === 'month') {
    return 'Mostrando ate 5 agendamentos do mes selecionado.';
  }

  if (type === 'week') {
    return 'Mostrando ate 5 agendamentos da semana selecionada.';
  }

  return 'Mostrando ate 5 agendamentos do dia selecionado.';
});

const upcomingFormatter = new Intl.DateTimeFormat('pt-BR', {
  weekday: 'short',
  day: '2-digit',
  month: '2-digit',
  hour: '2-digit',
  minute: '2-digit',
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
    start: (() => {
      const raw = item.scheduled_at_local ?? item.scheduled_at;
      const startDate = normalizeScheduledDate(raw);
      return startDate ? startDate.toISOString() : raw;
    })(),
    end: (() => {
      const endRaw = item.ended_at_local ?? item.ended_at;
      const endDate = normalizeScheduledDate(endRaw);
      if (endDate) {
        return endDate.toISOString();
      }

      const startRaw = item.scheduled_at_local ?? item.scheduled_at;
      const fallbackStart = normalizeScheduledDate(startRaw);
      if (!fallbackStart) {
        return null;
      }

      return new Date(fallbackStart.getTime() + (resolveDuration(item) ?? 60) * 60000).toISOString();
    })(),
  })),
}));

const upcoming = computed(() => {
  const now = new Date();
  const toleranceMs = 5 * 60 * 1000;
  const range = filterRange.value;
  const type = filterType.value;

  const normalized = appointments.value
    .map((item) => ({
      ...item,
      scheduledDate: normalizeScheduledDate(item.scheduled_at_local ?? item.scheduled_at),
      statusKey: String(item.status ?? '').trim().toLowerCase(),
    }))
    .filter(
      (item) =>
        item.scheduledDate instanceof Date &&
        !Number.isNaN(item.scheduledDate?.getTime?.()) &&
        UPCOMING_ACCEPTED_STATUSES.includes(item.statusKey),
    );

  let filtered = normalized;

  if (type) {
    if (!range) {
      filtered = [];
    } else {
      filtered = normalized.filter(
        (item) =>
          item.scheduledDate.getTime() >= range.start.getTime() &&
          item.scheduledDate.getTime() <= range.end.getTime(),
      );
    }
  } else {
    filtered = normalized.filter(
      (item) => item.scheduledDate.getTime() >= now.getTime() - toleranceMs,
    );
  }

  return filtered
    .sort((a, b) => a.scheduledDate - b.scheduledDate)
    .slice(0, 5)
    .map((item) => ({
      ...item,
      when: upcomingFormatter.format(item.scheduledDate),
      sessionProgress: getSessionProgress(item),
      isPast: item.scheduledDate.getTime() <= now.getTime(),
    }));
});

function clearFilters() {
  filterType.value = '';
  const today = todayIso();
  filterMonth.value = today.slice(0, 7);
  filterDate.value = today;
}

async function fetchData() {
  loading.value = true;
  try {
    statusUpdateError.value = '';
    const forDate = todayIso();
    const { data } = await http.get('/appointments', {
      params: { per_page: 100, for_date: forDate, from: forDate },
    });
    appointments.value = data.data ?? [];
    const metrics = data.extra?.metrics ?? null;
    if (metrics) {
      applyKpis(metrics);
    } else {
      applyKpis(calculateLocalKpis(appointments.value, forDate));
    }
  } finally {
    loading.value = false;
  }
}

function applyKpis(metrics) {
  kpis.value = {
    occupancy: Number(metrics.occupancy_rate ?? metrics.occupancy ?? 0),
    noShow: Number(metrics.no_show_rate ?? metrics.noShow ?? 0),
    ticket: Number(metrics.avg_ticket ?? metrics.ticket ?? 0),
  };
}

function calculateLocalKpis(list, isoDate) {
  const sameDay = list.filter((item) => extractIsoDate(item.scheduled_at_local ?? item.scheduled_at) === isoDate);
  const relevant = sameDay.filter((item) => KPI_ACCEPTED_STATUSES.has(String(item.status ?? '').toLowerCase()));
  const totalRelevant = relevant.length;

  const totalMinutes = relevant.reduce(
    (total, item) => total + resolveDuration(item),
    0,
  );

  const professionalIds = new Set(
    relevant.map((item) => item.professional_id).filter((value) => value !== null && value !== undefined),
  );

  const professionalsCount = professionalIds.size || (totalMinutes > 0 ? 1 : 0);
  const capacityMinutes = professionalsCount * 8 * 60;
  const occupancyRate = capacityMinutes > 0 ? Math.min(100, (totalMinutes / capacityMinutes) * 100) : 0;

  const noShowCount = relevant.filter((item) => String(item.status).toLowerCase() === 'no_show').length;
  const noShowRate = totalRelevant > 0 ? (noShowCount / totalRelevant) * 100 : 0;

  const ticketValues = relevant
    .map((item) => Number(item.service?.list_price ?? 0))
    .filter((value) => !Number.isNaN(value) && value > 0);

  const avgTicket =
    ticketValues.length > 0
      ? ticketValues.reduce((total, value) => total + value, 0) / ticketValues.length
      : 0;

  return {
    occupancy_rate: Number(occupancyRate.toFixed(1)),
    no_show_rate: Number(noShowRate.toFixed(1)),
    avg_ticket: Number(avgTicket.toFixed(2)),
  };
}

function resolveDuration(item) {
  if (item?.duration_min) {
    return Number(item.duration_min);
  }
  if (item?.service?.duration_min) {
    return Number(item.service.duration_min);
  }
  return 60;
}

async function markAttendance(appointmentId, status) {
  if (updatingStatusId.value === appointmentId) {
    return;
  }

  statusUpdateError.value = '';
  updatingStatusId.value = appointmentId;

  try {
    await http.patch(`/appointments/${appointmentId}/status`, { status });
    await fetchData();
  } catch (error) {
    statusUpdateError.value =
      error.response?.data?.message ?? 'Nao foi possivel atualizar o status do atendimento.';
  } finally {
    updatingStatusId.value = null;
  }
}

function getStatusLabel(status) {
  return STATUS_LABELS[String(status ?? '').toLowerCase()] ?? 'Indefinido';
}

function getStatusVariant(status) {
  return STATUS_VARIANTS[String(status ?? '').toLowerCase()] ?? 'secondary';
}

function getSessionProgress(appointment) {
  const current = Number(appointment.package_session_number ?? 0);
  const total = Number(appointment.client_package?.package?.sessions_count ?? 0);

  if (!current || !total) {
    return null;
  }

  return `${current}/${total} sessoes`;
}
function capitalize(value) {
  if (!value) {
    return '';
  }

  return `${value.charAt(0).toUpperCase()}${value.slice(1)}`;
}

function formatDayMonth(date) {
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
    return '';
  }

  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
  });
}
function formatPercent(value) {
  return Number(value ?? 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 1,
  });
}

function formatCurrency(value) {
  return Number(value ?? 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function extractIsoDate(value) {
  if (typeof value !== 'string') {
    return null;
  }

  return value.slice(0, 10);
}

function todayIso() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function normalizeScheduledDate(value) {
  if (!value) {
    return null;
  }

  if (value instanceof Date) {
    return value;
  }

  if (typeof value === 'number') {
    return new Date(value);
  }

  if (typeof value === 'string') {
    const trimmed = value.trim();
    if (!trimmed) {
      return null;
    }

    const hasTimezone = /([+-]\d{2}:\d{2}|Z)$/i.test(trimmed);
    if (hasTimezone) {
      const date = new Date(trimmed);
      if (!Number.isNaN(date.getTime())) {
        return date;
      }
    }

    const normalized = trimmed.replace(' ', 'T');
    const [datePart, timePartRaw = '00:00'] = normalized.split('T');
    if (!datePart) {
      return null;
    }

    const timePart = timePartRaw.split('.')[0];
    const [year, month, day] = datePart.split('-').map(Number);
    const [hour = 0, minute = 0] = timePart.split(':').map(Number);

    if ([year, month, day].some((valuePart) => Number.isNaN(valuePart))) {
      return null;
    }

    return new Date(year, (month ?? 1) - 1, day, hour, minute);
  }

  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? null : date;
}

async function onAppointmentCreated() {
  showNewAppointment.value = false;
  await fetchData();
}

onMounted(() => {
  fetchData();
  window.addEventListener('appointments:refresh', fetchData);
});

onBeforeUnmount(() => {
  window.removeEventListener('appointments:refresh', fetchData);
});
</script>

