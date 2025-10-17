<template>
  <section class="container-fluid px-0 px-md-2">
    <div class="card card-mobile p-3 mb-3">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h2 class="h5 fw-semibold mb-1">Clientes & relacionamento</h2>
          <small class="text-body-secondary">Pesquise, classifique e ative clientes com um toque.</small>
        </div>
        <div class="input-group input-group-lg">
          <span class="input-group-text bg-body-secondary"><i class="fa-solid fa-magnifying-glass"></i></span>
          <input
            v-model="filters.search"
            type="search"
            class="form-control"
            placeholder="Busque por nome, telefone ou e-mail"
          />
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <div class="card card-mobile p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 fw-semibold mb-0">Clientes ativos</h3>
            <button class="btn btn-primary btn-sm" type="button">
              <i class="fa-regular fa-user-plus me-2"></i>Novo cliente
            </button>
          </div>

          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
          </div>
          <div v-else class="d-flex flex-column gap-2">
            <article
              v-for="client in clients"
              :key="client.id"
              class="border rounded-4 px-3 py-3 d-flex flex-column gap-2"
            >
              <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div>
                  <h4 class="h6 fw-semibold mb-0">{{ client.full_name }}</h4>
                  <small class="text-body-secondary">
                    {{ client.phone }} - {{ client.email ?? 'sem e-mail' }}
                  </small>
                </div>
                <span class="badge text-bg-light text-body-secondary">{{ client.source ?? 'Organico' }}</span>
              </div>

              <div class="d-flex flex-wrap gap-3 small text-body-secondary">
                <span>
                  <i class="fa-regular fa-calendar-check me-1"></i>{{ formatDate(client.last_appointment_at) }}
                </span>
                <span>
                  <i class="fa-solid fa-trophy me-1"></i>{{ client.tags?.join(', ') ?? 'Sem tags' }}
                </span>
              </div>

              <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary btn-sm flex-grow-1" type="button">
                  <i class="fa-brands fa-whatsapp me-1"></i>Enviar WhatsApp
                </button>
                <button class="btn btn-outline-secondary btn-sm" type="button">
                  Detalhes
                </button>
                <button
                  v-if="canManageAppointments"
                  class="btn btn-outline-warning btn-sm"
                  type="button"
                  @click="openManageAppointments(client)"
                >
                  <i class="fa-regular fa-calendar-xmark me-1"></i>Gerenciar agenda
                </button>
              </div>
            </article>
            <p v-if="!clients.length" class="text-body-secondary text-center py-4 mb-0">
              Nenhum cliente encontrado.
            </p>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <div class="card card-mobile p-3 mb-3">
          <h3 class="h6 fw-semibold mb-3">Resumo rapido</h3>
          <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
            <li class="d-flex justify-content-between">
              <span>Clientes ativos</span><strong>{{ stats.total }}</strong>
            </li>
            <li class="d-flex justify-content-between">
              <span>Reativacoes 30d</span><strong>{{ stats.reengagement }}</strong>
            </li>
            <li class="d-flex justify-content-between">
              <span>Opt-in marketing</span><strong>{{ stats.optIn }}%</strong>
            </li>
          </ul>
        </div>

        <div class="card card-mobile p-3">
          <h3 class="h6 fw-semibold mb-3">Listas inteligentes</h3>
          <div class="d-flex flex-column gap-2">
            <button class="btn btn-light border text-start" type="button">
              <div class="fw-semibold">Sem retorno ha 30 dias</div>
              <small class="text-body-secondary">Planeje um ping pessoal</small>
            </button>
            <button class="btn btn-light border text-start" type="button">
              <div class="fw-semibold">Vence pacote em 7 dias</div>
              <small class="text-body-secondary">Revise plano de continuidade</small>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <teleport to="body">
    <div
      v-if="showManageModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(15, 23, 42, 0.45)"
      @click.self="closeManageModal"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <div class="d-flex flex-column">
              <h5 class="modal-title">Agenda de {{ managingClient?.full_name ?? 'cliente' }}</h5>
              <small class="text-body-secondary">
                Selecione a sessao e escolha cancelar ou remarcar de forma segura.
              </small>
            </div>
            <button type="button" class="btn-close" aria-label="Fechar" @click="closeManageModal" />
          </div>
          <div class="modal-body">
            <div v-if="loadingAppointments" class="text-center py-4">
              <div class="spinner-border text-primary" role="status" />
            </div>
            <template v-else>
              <p v-if="appointmentError" class="text-danger small mb-3">{{ appointmentError }}</p>
              <div v-if="clientAppointments.length" class="d-flex flex-column gap-3">
                <article
                  v-for="appointment in clientAppointments"
                  :key="appointment.id"
                  class="border rounded-3 p-3 d-flex flex-column gap-2"
                >
                  <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                      <strong class="d-block">{{ appointment.service?.name ?? 'Servico' }}</strong>
                      <small class="text-body-secondary">
                        {{ formatAppointmentDatetime(appointment.scheduled_at_local ?? appointment.scheduled_at) }}
                      </small>
                    </div>
                    <span class="badge text-bg-light text-capitalize">{{ formatStatus(appointment.status) }}</span>
                  </div>

                  <div class="d-flex flex-wrap gap-2">
                    <button
                      class="btn btn-outline-warning btn-sm"
                      type="button"
                      :disabled="!canRescheduleAppointment(appointment)"
                      @click="openReschedule(appointment)"
                    >
                      <i class="fa-regular fa-calendar-days me-1"></i>Remarcar
                    </button>
                    <button
                      class="btn btn-outline-danger btn-sm"
                      type="button"
                      :disabled="!canCancelAppointment(appointment)"
                      @click="confirmCancel(appointment)"
                    >
                      <i class="fa-regular fa-circle-xmark me-1"></i>Cancelar
                    </button>
                  </div>
                </article>
              </div>
              <p v-else class="text-body-secondary text-center mb-0">
                Nenhum agendamento futuro encontrado para este cliente.
              </p>
            </template>
          </div>
        </div>
      </div>
    </div>
  </teleport>

  <teleport to="body">
    <div
      v-if="rescheduleState.show"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(15, 23, 42, 0.45)"
      @click.self="closeReschedule"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form @submit.prevent="submitReschedule">
            <div class="modal-header">
              <h5 class="modal-title">Remarcar sessao</h5>
              <button type="button" class="btn-close" aria-label="Fechar" @click="closeReschedule" />
            </div>
            <div class="modal-body">
              <p class="small text-body-secondary mb-3">
                Ajuste a data/horario conforme o novo combinado. A disponibilidade do profissional e o limite de 24h sao validados automaticamente para clientes.
              </p>
              <div class="mb-3">
                <label class="form-label small text-body-secondary">Nova data e hora</label>
                <input v-model="rescheduleState.datetime" type="datetime-local" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label small text-body-secondary">Duracao (minutos)</label>
                <input
                  v-model.number="rescheduleState.duration"
                  type="number"
                  min="15"
                  max="600"
                  step="5"
                  class="form-control"
                  required
                />
              </div>
              <p v-if="rescheduleState.error" class="text-danger small mb-0">{{ rescheduleState.error }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="closeReschedule" :disabled="rescheduleState.saving">
                Voltar
              </button>
              <button type="submit" class="btn btn-primary" :disabled="rescheduleState.saving">
                <span v-if="rescheduleState.saving" class="spinner-border spinner-border-sm me-2" role="status" />
                Confirmar remarcacao
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import http from '../services/http';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const clients = ref([]);
const loading = ref(false);
const filters = reactive({
  search: '',
});

const stats = reactive({
  total: 0,
  reengagement: 0,
  optIn: 0,
});

const managingClient = ref(null);
const showManageModal = ref(false);
const clientAppointments = ref([]);
const loadingAppointments = ref(false);
const appointmentError = ref('');

const rescheduleState = reactive({
  show: false,
  appointment: null,
  datetime: '',
  duration: 60,
  saving: false,
  error: '',
});

const canManageAppointments = computed(() => {
  const role = auth.user?.role ?? '';
  return ['owner', 'reception'].includes(role.toLowerCase());
});

const cancellableStatuses = new Set(['pending', 'confirmed']);
const reschedulableStatuses = new Set(['pending', 'confirmed']);

function formatDate(value) {
  if (!value) {
    return 'Sem sessoes';
  }
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: 'short',
  }).format(new Date(value));
}

let debounceTimer;
watch(
  () => filters.search,
  () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchClients(), 350);
  },
);

async function fetchClients(page = 1) {
  loading.value = true;
  try {
    const { data } = await http.get('/clients', {
      params: {
        page,
        search: filters.search || undefined,
        per_page: 20,
      },
    });
    clients.value = data.data ?? [];
    stats.total = data.meta?.total ?? clients.value.length;
    stats.optIn = Math.round(
      ((clients.value.filter((client) => client.consent_marketing).length || 0) / (clients.value.length || 1)) * 100,
    );
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchClients();
  window.addEventListener('appointments:refresh', refreshAfterAppointmentUpdate);
});

function openManageAppointments(client) {
  managingClient.value = client;
  showManageModal.value = true;
  fetchClientAppointments(client.id);
}

function closeManageModal() {
  showManageModal.value = false;
  managingClient.value = null;
  clientAppointments.value = [];
  appointmentError.value = '';
}

async function fetchClientAppointments(clientId) {
  if (!clientId) {
    return;
  }
  loadingAppointments.value = true;
  appointmentError.value = '';
  try {
    const today = todayIso();
    const { data } = await http.get('/appointments', {
      params: {
        client_id: clientId,
        from: today,
        per_page: 20,
      },
    });
    clientAppointments.value = data.data ?? [];
  } catch (error) {
    appointmentError.value = error.response?.data?.message ?? 'Nao foi possivel carregar os agendamentos.';
  } finally {
    loadingAppointments.value = false;
  }
}

function confirmCancel(appointment) {
  if (!appointment) {
    return;
  }
  const clientName = appointment.client?.full_name ?? 'o cliente';
  const ok = window.confirm(`Confirmar cancelamento da sessao de ${clientName}?`);
  if (!ok) {
    return;
  }

  cancelAppointment(appointment);
}

async function cancelAppointment(appointment) {
  appointmentError.value = '';
  try {
    await http.patch(`/appointments/${appointment.id}/status`, { status: 'cancelled' });
    await fetchClientAppointments(managingClient.value?.id);
    window.dispatchEvent(new CustomEvent('appointments:refresh'));
  } catch (error) {
    appointmentError.value = error.response?.data?.message ?? 'Falha ao cancelar agendamento.';
  }
}

function openReschedule(appointment) {
  if (!appointment) {
    return;
  }

  rescheduleState.appointment = appointment;
  rescheduleState.duration = Number(appointment.duration_min ?? appointment.service?.duration_min ?? 60);
  rescheduleState.datetime = toLocalInput(appointment.scheduled_at_local ?? appointment.scheduled_at);
  rescheduleState.error = '';
  rescheduleState.show = true;
}

function closeReschedule() {
  rescheduleState.show = false;
  rescheduleState.appointment = null;
  rescheduleState.error = '';
  rescheduleState.datetime = '';
  rescheduleState.duration = 60;
}

async function submitReschedule() {
  if (!rescheduleState.appointment) {
    return;
  }
  rescheduleState.error = '';

  const payload = buildUpdatePayload(rescheduleState.appointment, rescheduleState.datetime, rescheduleState.duration);
  if (!payload) {
    rescheduleState.error = 'Informe uma data e hora validas.';
    return;
  }

  rescheduleState.saving = true;
  try {
    await http.put(`/appointments/${rescheduleState.appointment.id}`, payload);
    await fetchClientAppointments(managingClient.value?.id);
    window.dispatchEvent(new CustomEvent('appointments:refresh'));
    closeReschedule();
  } catch (error) {
    rescheduleState.error =
      error.response?.data?.errors?.scheduled_at?.[0] ??
      error.response?.data?.message ??
      'Falha ao remarcar o agendamento.';
  } finally {
    rescheduleState.saving = false;
  }
}

function buildUpdatePayload(appointment, datetimeLocal, duration) {
  const iso = toApiDateTime(datetimeLocal);
  if (!iso) {
    return null;
  }

  const clientPackage = appointment.client_package ?? null;
  const service = appointment.service ?? null;
  const professional = appointment.professional ?? null;

  if (!clientPackage?.id || !service?.id || !professional?.id) {
    rescheduleState.error = 'Agendamento nao possui dados suficientes para remarcacao.';
    return null;
  }

  return {
    client_id: appointment.client?.id ?? appointment.client_id,
    client_package_id: clientPackage.id,
    professional_id: professional.id,
    room_id: appointment.room_id ?? null,
    equipment_id: appointment.equipment_id ?? null,
    service_id: service.id,
    scheduled_at: iso,
    duration_min: Number(duration ?? appointment.duration_min ?? service.duration_min ?? 60),
    status: appointment.status ?? 'confirmed',
    source: appointment.source ?? null,
    notes: appointment.notes ?? null,
  };
}

function toLocalInput(value) {
  const date = value ? new Date(value) : null;
  if (!date || Number.isNaN(date.getTime())) {
    return '';
  }
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day}T${hours}:${minutes}`;
}

function toApiDateTime(datetimeLocal) {
  if (!datetimeLocal) {
    return null;
  }
  const [datePart, timePart] = datetimeLocal.split('T');
  if (!datePart || !timePart) {
    return null;
  }

  const [year, month, day] = datePart.split('-').map(Number);
  const [hour, minute] = timePart.split(':').map(Number);

  if ([year, month, day, hour, minute].some((part) => Number.isNaN(part))) {
    return null;
  }

  const date = new Date(year, month - 1, day, hour, minute, 0);
  const offsetMinutes = -date.getTimezoneOffset();
  const sign = offsetMinutes >= 0 ? '+' : '-';
  const absOffset = Math.abs(offsetMinutes);
  const offsetHours = String(Math.floor(absOffset / 60)).padStart(2, '0');
  const offsetMins = String(absOffset % 60).padStart(2, '0');

  return `${datePart}T${timePart}:00${sign}${offsetHours}:${offsetMins}`;
}

function todayIso() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function formatAppointmentDatetime(value) {
  if (!value) {
    return 'Sem data';
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return 'Data invalida';
  }
  return new Intl.DateTimeFormat('pt-BR', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

function formatStatus(status) {
  const normalized = String(status ?? '').toLowerCase();
  const labels = {
    pending: 'Pendente',
    confirmed: 'Confirmado',
    cancelled: 'Cancelado',
    completed: 'Concluido',
    no_show: 'No-show',
    noshow: 'No-show',
  };
  return labels[normalized] ?? 'Indefinido';
}

function canCancelAppointment(appointment) {
  const normalized = String(appointment.status ?? '').toLowerCase();
  return cancellableStatuses.has(normalized);
}

function canRescheduleAppointment(appointment) {
  const normalized = String(appointment.status ?? '').toLowerCase();
  return reschedulableStatuses.has(normalized);
}

function refreshAfterAppointmentUpdate() {
  if (managingClient.value) {
    fetchClientAppointments(managingClient.value.id);
  }
}

onBeforeUnmount(() => {
  window.removeEventListener('appointments:refresh', refreshAfterAppointmentUpdate);
});
</script>
