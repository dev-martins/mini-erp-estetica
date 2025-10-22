<template>
  <section class="container-fluid px-0 px-md-2">
    <div class="card card-mobile p-3 mb-3">
      <div class="d-flex flex-column gap-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <h2 class="h5 fw-semibold mb-1">Clientes & relacionamento</h2>
            <small class="text-body-secondary">Pesquise, classifique e ative clientes com um toque.</small>
          </div>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
          <div class="input-group input-group-lg flex-grow-1">
            <span class="input-group-text bg-body-secondary">
              <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input
              v-model="filters.search"
              type="search"
              class="form-control"
              placeholder="Busque por nome, telefone ou e-mail"
              aria-label="Buscar clientes"
            />
          </div>
          <div class="status-filter flex-shrink-0">
            <select
              id="client-status-filter"
              v-model="filters.status"
              class="form-select form-select-lg"
              aria-label="Filtrar clientes por status"
            >
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <div class="card card-mobile p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 fw-semibold mb-0">{{ listTitle }}</h3>
            <button class="btn btn-primary btn-sm" type="button" @click="openCreateModal">
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
                <div class="d-flex flex-column gap-1">
                  <h4 class="h6 fw-semibold mb-0">{{ client.full_name }}</h4>
                  <small class="text-body-secondary">
                    {{ client.phone }} - {{ client.email ?? 'sem e-mail' }}
                  </small>
                  <span
                    v-if="client.status === 'inactive'"
                    class="badge text-bg-warning align-self-start"
                  >
                    Inativo
                  </span>
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
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="openDetails(client)">
                  Detalhes
                </button>
                <button
                  v-if="client.status === 'active'"
                  class="btn btn-outline-danger btn-sm"
                  type="button"
                  :disabled="isStatusProcessing(client.id)"
                  @click="updateClientStatus(client, 'inactive')"
                >
                  <span
                    v-if="isStatusProcessing(client.id)"
                    class="spinner-border spinner-border-sm me-1"
                    role="status"
                  ></span>
                  Desativar
                </button>
                <button
                  v-else
                  class="btn btn-outline-success btn-sm"
                  type="button"
                  :disabled="isStatusProcessing(client.id)"
                  @click="updateClientStatus(client, 'active')"
                >
                  <span
                    v-if="isStatusProcessing(client.id)"
                    class="spinner-border spinner-border-sm me-1"
                    role="status"
                  ></span>
                  Reativar
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

          <div
            v-if="showPagination"
            class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-3"
          >
            <small class="text-body-secondary">
              Pagina {{ pagination.current_page }} de {{ pagination.last_page }}
            </small>
            <nav aria-label="Paginacao de clientes">
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: pagination.current_page <= 1 || loading }">
                  <button
                    class="page-link"
                    type="button"
                    aria-label="Pagina anterior"
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1 || loading"
                  >
                    ‹
                  </button>
                </li>
                <li
                  v-for="pageNumber in pageNumbers"
                  :key="pageNumber"
                  class="page-item"
                  :class="{ active: pageNumber === pagination.current_page }"
                >
                  <button
                    class="page-link"
                    type="button"
                    @click="changePage(pageNumber)"
                    :disabled="pageNumber === pagination.current_page || loading"
                  >
                    {{ pageNumber }}
                  </button>
                </li>
                <li
                  class="page-item"
                  :class="{ disabled: pagination.current_page >= pagination.last_page || loading }"
                >
                  <button
                    class="page-link"
                    type="button"
                    aria-label="Proxima pagina"
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page || loading"
                  >
                    ›
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <div class="card card-mobile p-3 mb-3">
          <h3 class="h6 fw-semibold mb-3">Resumo rapido</h3>
          <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
            <li class="d-flex justify-content-between">
              <span>{{ listTitle }}</span><strong>{{ stats.total }}</strong>
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
      v-if="showDetailsModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(15, 23, 42, 0.45)"
      @click.self="closeDetails"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-fullscreen-sm-down">
        <div class="modal-content rounded-4">
          <div class="modal-header">
            <h5 class="modal-title">Detalhes do cliente</h5>
            <button type="button" class="btn-close" aria-label="Fechar detalhes" @click="closeDetails"></button>
          </div>
          <div class="modal-body">
            <div v-if="detailsLoading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div v-else>
              <p v-if="detailsError" class="alert alert-warning mb-0">{{ detailsError }}</p>
              <div v-else-if="selectedClient" class="d-flex flex-column gap-3">
                <div class="d-flex flex-column gap-1">
                  <h6 class="fw-semibold mb-0">{{ selectedClient.full_name }}</h6>
                  <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge text-bg-light text-body-secondary">
                      {{ formatStatusLabel(selectedClient.status) }}
                    </span>
                    <span v-if="selectedClient.source" class="badge text-bg-secondary-subtle text-body-secondary">
                      {{ selectedClient.source }}
                    </span>
                  </div>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 gy-2 small">
                  <div class="col">
                    <span class="text-body-secondary d-block">Telefone</span>
                    <span class="fw-semibold">{{ selectedClient.phone ?? 'Nao informado' }}</span>
                  </div>
                  <div class="col">
                    <span class="text-body-secondary d-block">E-mail</span>
                    <span class="fw-semibold">{{ selectedClient.email ?? 'Nao informado' }}</span>
                  </div>
                  <div class="col">
                    <span class="text-body-secondary d-block">Instagram</span>
                    <span class="fw-semibold">{{ selectedClient.instagram ?? 'Nao informado' }}</span>
                  </div>
                  <div class="col">
                    <span class="text-body-secondary d-block">Ultima sessao</span>
                    <span class="fw-semibold">{{ formatDetailedDate(selectedClient.last_appointment_at) }}</span>
                  </div>
                  <div class="col">
                    <span class="text-body-secondary d-block">Criado em</span>
                    <span class="fw-semibold">{{ formatDetailedDate(selectedClient.created_at) }}</span>
                  </div>
                  <div class="col">
                    <span class="text-body-secondary d-block">Marketing</span>
                    <span class="fw-semibold">{{ selectedClient.consent_marketing ? 'Opt-in' : 'Opt-out' }}</span>
                  </div>
                  <div class="col-12">
                    <span class="text-body-secondary d-block">Tags</span>
                    <span class="fw-semibold">
                      {{
                        selectedClient.tags?.length
                          ? selectedClient.tags.join(', ')
                          : 'Sem tags'
                      }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDetails">Fechar</button>
          </div>
        </div>
      </div>
    </div>
  </teleport>

  <teleport to="body">
    <div
      v-if="showCreateModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(15, 23, 42, 0.45)"
      @click.self="closeCreateModal"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-fullscreen-sm-down">
        <div class="modal-content rounded-4">
          <form @submit.prevent="submitNewClient" novalidate>
            <div class="modal-header">
              <h5 class="modal-title">Cadastrar novo cliente</h5>
              <button type="button" class="btn-close" aria-label="Fechar cadastro" @click="closeCreateModal"></button>
            </div>
            <div class="modal-body">
              <div v-if="newClientErrors.general" class="alert alert-warning">{{ newClientErrors.general }}</div>
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label small text-body-secondary" for="client-full-name">Nome completo</label>
                  <input
                    id="client-full-name"
                    v-model="newClientForm.full_name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': newClientErrors.full_name }"
                    placeholder="Ex.: Ana Paula Silva"
                    required
                  />
                  <div v-if="newClientErrors.full_name" class="invalid-feedback">
                    {{ newClientErrors.full_name }}
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label small text-body-secondary" for="client-phone">Telefone</label>
                  <input
                    id="client-phone"
                    v-model="newClientForm.phone"
                    type="tel"
                    class="form-control"
                    :class="{ 'is-invalid': newClientErrors.phone }"
                    placeholder="(11) 99999-9999"
                  />
                  <div v-if="newClientErrors.phone" class="invalid-feedback">
                    {{ newClientErrors.phone }}
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label small text-body-secondary" for="client-email">E-mail</label>
                  <input
                    id="client-email"
                    v-model="newClientForm.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': newClientErrors.email }"
                    placeholder="contato@cliente.com"
                  />
                  <div v-if="newClientErrors.email" class="invalid-feedback">
                    {{ newClientErrors.email }}
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label small text-body-secondary" for="client-password">Senha</label>
                  <input
                    id="client-password"
                    v-model="newClientForm.password"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': newClientErrors.password }"
                    placeholder="Minimo 8 caracteres"
                    required
                  />
                  <div v-if="newClientErrors.password" class="invalid-feedback">
                    {{ newClientErrors.password }}
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label small text-body-secondary" for="client-birthdate">Data de nascimento</label>
                  <input
                    id="client-birthdate"
                    v-model="newClientForm.birthdate"
                    type="date"
                    class="form-control"
                  />
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label small text-body-secondary" for="client-instagram">Instagram</label>
                  <input
                    id="client-instagram"
                    v-model="newClientForm.instagram"
                    type="text"
                    class="form-control"
                    placeholder="@perfil"
                  />
                </div>
                <div class="col-12">
                  <label class="form-label small text-body-secondary" for="client-source">Origem</label>
                  <input
                    id="client-source"
                    v-model="newClientForm.source"
                    type="text"
                    class="form-control"
                    placeholder="Ex.: Instagram, Indicacao"
                  />
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input
                      id="client-consent"
                      v-model="newClientForm.consent_marketing"
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label small text-body-secondary" for="client-consent">
                      Aceita receber comunicados de marketing
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary" @click="closeCreateModal">Cancelar</button>
              <button class="btn btn-primary" type="submit" :disabled="savingClient">
                <span
                  v-if="savingClient"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                Salvar cliente
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </teleport>

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
  status: 'active',
});

const statusOptions = [
  { value: 'active', label: 'Ativos' },
  { value: 'inactive', label: 'Inativos' },
  { value: 'all', label: 'Todos' },
];

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 5,
});

const listTitle = computed(() => {
  switch (filters.status) {
    case 'inactive':
      return 'Clientes inativos';
    case 'all':
      return 'Todos os clientes';
    default:
      return 'Clientes ativos';
  }
});

const showPagination = computed(() => pagination.last_page > 1);

const pageNumbers = computed(() => {
  const total = pagination.last_page ?? 1;
  const current = pagination.current_page ?? 1;

  if (total <= 1) {
    return [1];
  }

  const numbers = new Set([1, total, current - 1, current, current + 1]);

  return Array.from(numbers)
    .filter((value) => value >= 1 && value <= total)
    .sort((a, b) => a - b);
});

const stats = reactive({
  total: 0,
  reengagement: 0,
  optIn: 0,
});

const showDetailsModal = ref(false);
const selectedClient = ref(null);
const detailsLoading = ref(false);
const detailsError = ref('');

const showCreateModal = ref(false);
const savingClient = ref(false);
const newClientForm = reactive({
  full_name: '',
  phone: '',
  email: '',
  password: '',
  birthdate: '',
  instagram: '',
  source: '',
  consent_marketing: false,
});
const newClientErrors = reactive({
  full_name: '',
  phone: '',
  email: '',
  password: '',
  general: '',
});

const statusProcessing = ref([]);

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

function formatDetailedDate(value) {
  if (!value) {
    return 'Nao informado';
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return 'Data invalida';
  }
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

function formatStatusLabel(status) {
  return status === 'inactive' ? 'Inativo' : 'Ativo';
}

let debounceTimer;
watch(
  () => filters.search,
  () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchClients(1), 350);
  },
);

watch(
  () => filters.status,
  () => {
    fetchClients(1);
  },
);

async function fetchClients(page = 1) {
  loading.value = true;
  try {
    const { data } = await http.get('/clients', {
      params: {
        page,
        search: filters.search || undefined,
        status: filters.status,
        per_page: pagination.per_page,
      },
    });

    clients.value = data.data ?? [];

    const meta = data.meta ?? {};

    pagination.current_page = meta.current_page ?? page;
    pagination.last_page = meta.last_page ?? 1;
    pagination.total = meta.total ?? clients.value.length;
    pagination.per_page = meta.per_page ?? pagination.per_page;

    stats.total = pagination.total;
    const pageTotal = clients.value.length || 1;
    stats.optIn = Math.round(
      ((clients.value.filter((client) => client.consent_marketing).length || 0) / pageTotal) * 100,
    );
  } finally {
    loading.value = false;
  }
}

function changePage(target) {
  const lastPage = pagination.last_page ?? 1;
  const current = pagination.current_page ?? 1;

  if (target < 1 || target > lastPage || target === current || loading.value) {
    return;
  }

  fetchClients(target);
}

function resetNewClientForm() {
  newClientForm.full_name = '';
  newClientForm.phone = '';
  newClientForm.email = '';
  newClientForm.password = '';
  newClientForm.birthdate = '';
  newClientForm.instagram = '';
  newClientForm.source = '';
  newClientForm.consent_marketing = false;
}

function resetNewClientErrors() {
  newClientErrors.full_name = '';
  newClientErrors.phone = '';
  newClientErrors.email = '';
  newClientErrors.password = '';
  newClientErrors.general = '';
}

function openCreateModal() {
  resetNewClientForm();
  resetNewClientErrors();
  showCreateModal.value = true;
}

function closeCreateModal() {
  showCreateModal.value = false;
}

async function submitNewClient() {
  resetNewClientErrors();

  if (!newClientForm.full_name.trim()) {
    newClientErrors.full_name = 'Informe o nome completo.';
    return;
  }

  if (!newClientForm.phone.trim() && !newClientForm.email.trim()) {
    newClientErrors.general = 'Informe telefone ou e-mail.';
    return;
  }

  if (!newClientForm.password.trim()) {
    newClientErrors.password = 'Informe uma senha.';
    return;
  }

  savingClient.value = true;

  const payload = {
    full_name: newClientForm.full_name.trim(),
    phone: newClientForm.phone.trim() || null,
    email: newClientForm.email.trim() || null,
    password: newClientForm.password,
    birthdate: newClientForm.birthdate || null,
    instagram: newClientForm.instagram.trim() || null,
    source: newClientForm.source.trim() || null,
    consent_marketing: Boolean(newClientForm.consent_marketing),
  };

  try {
    await http.post('/clients', payload);
    showCreateModal.value = false;
    resetNewClientForm();
    fetchClients(1);
  } catch (error) {
    const responseErrors = error.response?.data?.errors;
    if (responseErrors) {
      Object.entries(responseErrors).forEach(([field, messages]) => {
        if (Object.prototype.hasOwnProperty.call(newClientErrors, field)) {
          newClientErrors[field] = Array.isArray(messages) ? messages[0] : messages;
        }
      });
      if (!newClientErrors.general && responseErrors.general) {
        newClientErrors.general = Array.isArray(responseErrors.general)
          ? responseErrors.general[0]
          : responseErrors.general;
      }
    } else {
      newClientErrors.general = error.response?.data?.message ?? 'Falha ao cadastrar cliente.';
    }
    return;
  } finally {
    savingClient.value = false;
  }
}

function setStatusProcessing(clientId, processing) {
  if (processing) {
    if (!statusProcessing.value.includes(clientId)) {
      statusProcessing.value = [...statusProcessing.value, clientId];
    }
    return;
  }

  statusProcessing.value = statusProcessing.value.filter((value) => value !== clientId);
}

function isStatusProcessing(clientId) {
  return statusProcessing.value.includes(clientId);
}

async function updateClientStatus(client, targetStatus) {
  if (!client?.id || targetStatus === client.status) {
    return;
  }

  setStatusProcessing(client.id, true);
  try {
    await http.patch(`/clients/${client.id}/status`, { status: targetStatus });
    fetchClients(pagination.current_page);
  } catch (error) {
    console.error('Falha ao atualizar status do cliente', error);
  } finally {
    setStatusProcessing(client.id, false);
  }
}

async function openDetails(client) {
  if (!client?.id) {
    return;
  }

  selectedClient.value = client;
  detailsError.value = '';
  showDetailsModal.value = true;
  detailsLoading.value = true;

  try {
    const { data } = await http.get(`/clients/${client.id}`);
    selectedClient.value = data.data ?? data;
  } catch (error) {
    detailsError.value = error.response?.data?.message ?? 'Falha ao carregar detalhes do cliente.';
  } finally {
    detailsLoading.value = false;
  }
}

function closeDetails() {
  showDetailsModal.value = false;
  selectedClient.value = null;
  detailsError.value = '';
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
