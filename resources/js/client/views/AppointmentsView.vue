<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import http from '../services/http';

const filter = ref('upcoming');
const appointments = ref([]);
const appointmentsLoading = ref(false);
const appointmentsError = ref(null);

const myPackages = ref([]);
const packagesLoading = ref(false);
const packagesError = ref(null);

const availablePackages = ref([]);
const availableLoading = ref(false);
const availableError = ref(null);

const professionals = ref([]);
const professionalsLoading = ref(false);
const professionalsError = ref(null);

const subscribingPackageId = ref(null);
const scheduleForm = reactive({
  client_package_id: '',
  professional_id: '',
  scheduled_at: '',
  notes: '',
  submitting: false,
  success: '',
  error: '',
});

const hasAppointments = computed(() => appointments.value.length > 0);
const hasPackages = computed(() => myPackages.value.some((pkg) => pkg.remaining_sessions > 0));
const selectedPackage = computed(() => {
  const id = Number(scheduleForm.client_package_id);
  return myPackages.value.find((pkg) => pkg.id === id);
});
const selectedService = computed(() => selectedPackage.value?.package?.service ?? null);

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

function formatCurrency(value) {
  if (value === null || value === undefined) {
    return 'Sob consulta';
  }
  const amount = Number(value);
  if (Number.isNaN(amount)) {
    return 'Sob consulta';
  }
  return amount.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

async function fetchAppointments() {
  appointmentsLoading.value = true;
  appointmentsError.value = null;

  try {
    const params = new URLSearchParams({ per_page: '20' });

    if (filter.value === 'upcoming') {
      params.set('status', 'pending,confirmed');
      params.set('from', new Date().toISOString().split('T')[0]);
    } else {
      params.set('status', 'done,cancelled,no_show');
      params.set('to', new Date().toISOString().split('T')[0]);
    }

    const { data } = await http.get('/appointments', { params });
    appointments.value = Array.isArray(data.data) ? data.data : [];
  } catch (err) {
    console.error(err);
    appointmentsError.value = 'Não foi possível recuperar os atendimentos. Tente novamente.';
    appointments.value = [];
  } finally {
    appointmentsLoading.value = false;
  }
}

async function fetchMyPackages() {
  packagesLoading.value = true;
  packagesError.value = null;

  try {
    const { data } = await http.get('/packages');
    myPackages.value = Array.isArray(data.data) ? data.data : [];
  } catch (err) {
    console.error(err);
    packagesError.value = 'Não foi possível carregar seus pacotes.';
    myPackages.value = [];
  } finally {
    packagesLoading.value = false;
  }
}

async function fetchAvailablePackages() {
  availableLoading.value = true;
  availableError.value = null;

  try {
    const { data } = await http.get('/packages/available');
    availablePackages.value = Array.isArray(data.data) ? data.data : [];
  } catch (err) {
    console.error(err);
    availableError.value = 'Não foi possível carregar as opções de pacotes.';
    availablePackages.value = [];
  } finally {
    availableLoading.value = false;
  }
}

async function fetchProfessionals() {
  professionalsLoading.value = true;
  professionalsError.value = null;

  try {
    const { data } = await http.get('/professionals');
    professionals.value = Array.isArray(data.data) ? data.data : [];
  } catch (err) {
    console.error(err);
    professionalsError.value = 'Não foi possível carregar os profissionais.';
    professionals.value = [];
  } finally {
    professionalsLoading.value = false;
  }
}

async function subscribePackage(packageId) {
  subscribingPackageId.value = packageId;
  try {
    await http.post('/packages', { package_id: packageId });
    await Promise.all([fetchMyPackages(), fetchAppointments()]);
  } catch (err) {
    console.error(err);
    window.alert(err.response?.data?.message ?? 'Não foi possível assinar o pacote.');
  } finally {
    subscribingPackageId.value = null;
  }
}

async function submitSchedule() {
  scheduleForm.submitting = true;
  scheduleForm.error = '';
  scheduleForm.success = '';

  try {
    const payload = {
      client_package_id: Number(scheduleForm.client_package_id),
      service_id: selectedPackage.value?.package?.service?.id,
      professional_id: Number(scheduleForm.professional_id),
      scheduled_at: new Date(scheduleForm.scheduled_at).toISOString(),
      notes: scheduleForm.notes || undefined,
    };

    if (!payload.client_package_id || !payload.service_id || !payload.professional_id || !scheduleForm.scheduled_at) {
      throw new Error('Preencha todas as informações obrigatórias antes de agendar.');
    }

    await http.post('/appointments', payload);
    scheduleForm.success = 'Sessão solicitada com sucesso. A clínica confirmará o horário em breve.';
    scheduleForm.client_package_id = '';
    scheduleForm.professional_id = '';
    scheduleForm.scheduled_at = '';
    scheduleForm.notes = '';

    await Promise.all([fetchAppointments(), fetchMyPackages()]);
  } catch (err) {
    console.error(err);
    scheduleForm.error = err.response?.data?.message
      ?? err.response?.data?.errors?.scheduled_at?.[0]
      ?? err.response?.data?.errors?.client_package_id?.[0]
      ?? err.response?.data?.errors?.professional_id?.[0]
      ?? err.message
      ?? 'Não foi possível registrar o agendamento.';
  } finally {
    scheduleForm.submitting = false;
  }
}

onMounted(async () => {
  await Promise.all([
    fetchAppointments(),
    fetchMyPackages(),
    fetchAvailablePackages(),
    fetchProfessionals(),
  ]);
});

watch(filter, () => {
  fetchAppointments();
});
</script>

<template>
  <div class="d-flex flex-column gap-4">
    <section class="card border-0 shadow-sm rounded-4">
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
    </section>

    <section>
      <h2 class="h6 fw-semibold mb-3">Pacotes ativos</h2>
      <div v-if="packagesLoading" class="card border-0 shadow-sm rounded-4">
        <div class="card-body py-4 text-center text-body-secondary">Carregando seus pacotes...</div>
      </div>
      <div v-else-if="packagesError" class="alert alert-warning">{{ packagesError }}</div>
      <div v-else-if="myPackages.length" class="d-flex flex-column gap-3">
        <div
          v-for="clientPackage in myPackages"
          :key="clientPackage.id"
          class="card border-0 shadow-sm rounded-4"
        >
          <div class="card-body p-4 d-flex flex-column gap-2">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="text-uppercase small fw-semibold text-primary mb-1">
                  {{ clientPackage.package.service?.name }}
                </p>
                <h3 class="h6 fw-semibold mb-1">{{ clientPackage.package.name }}</h3>
                <p class="small text-body-secondary mb-0">
                  {{ clientPackage.remaining_sessions }} sessão(ões) disponível(is)
                  <span v-if="clientPackage.package.sessions_count">
                    - pacote com {{ clientPackage.package.sessions_count }} sessões
                  </span>
                </p>
                <p v-if="clientPackage.expiry_at" class="small text-body-secondary mb-0">
                  Válido até {{ new Date(clientPackage.expiry_at).toLocaleDateString('pt-BR') }}
                </p>
              </div>
              <button
                class="btn btn-outline-primary btn-sm"
                type="button"
                @click="scheduleForm.client_package_id = String(clientPackage.id)"
              >
                Usar este pacote
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="card border-0 shadow-sm rounded-4">
        <div class="card-body py-4 text-center text-body-secondary">
          Você ainda não possui pacotes ativos. Veja as opções abaixo para começar.
        </div>
      </div>
    </section>

    <section>
      <h2 class="h6 fw-semibold mb-3">Assine um pacote</h2>
      <div v-if="availableLoading" class="card border-0 shadow-sm rounded-4">
        <div class="card-body py-4 text-center text-body-secondary">Carregando catálogos de pacotes...</div>
      </div>
      <div v-else-if="availableError" class="alert alert-warning">{{ availableError }}</div>
      <div v-else-if="availablePackages.length" class="row g-3">
        <div v-for="pkg in availablePackages" :key="pkg.id" class="col-12 col-md-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column gap-2">
              <div>
                <p class="text-uppercase small fw-semibold text-primary mb-1">
                  {{ pkg.service?.name ?? 'Serviço' }}
                </p>
                <h3 class="h6 fw-semibold mb-0">{{ pkg.name }}</h3>
              </div>
              <p class="text-body-secondary small mb-0">{{ pkg.description ?? 'Pacote especial para clientes da clínica.' }}</p>
              <ul class="list-unstyled small mb-0">
                <li>Sessões: {{ pkg.sessions_count ?? '-' }}</li>
                <li>Duração média: {{ pkg.service?.duration_min ?? 60 }} min</li>
                <li v-if="pkg.expiry_days">Validade: {{ pkg.expiry_days }} dias</li>
              </ul>
              <div class="d-flex justify-content-between align-items-center mt-auto">
                <strong class="text-primary">{{ formatCurrency(pkg.price) }}</strong>
                <button
                  class="btn btn-primary btn-sm"
                  type="button"
                  :disabled="subscribingPackageId === pkg.id"
                  @click="subscribePackage(pkg.id)"
                >
                  <span
                    v-if="subscribingPackageId === pkg.id"
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                    aria-hidden="true"
                  ></span>
                  Assinar pacote
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="card border-0 shadow-sm">
        <div class="card-body py-4 text-center text-body-secondary">
          Nenhum pacote disponível no momento. Volte em breve ou fale com a equipe.
        </div>
      </div>
    </section>

    <section>
      <h2 class="h6 fw-semibold mb-3">Agendar nova sessão</h2>
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          <div v-if="!hasPackages" class="alert alert-info mb-0">
            Assine um pacote para liberar o agendamento online.
          </div>
          <form v-else @submit.prevent="submitSchedule" novalidate class="d-flex flex-column gap-3">
            <div>
              <label for="client_package_id" class="form-label">Escolha o pacote</label>
              <select
                id="client_package_id"
                v-model="scheduleForm.client_package_id"
                class="form-select form-select-lg"
                required
              >
                <option value="" disabled>Selecione um pacote</option>
                <option
                  v-for="clientPackage in myPackages.filter((pkg) => pkg.remaining_sessions > 0)"
                  :key="clientPackage.id"
                  :value="clientPackage.id"
                >
                  {{ clientPackage.package.name }} - {{ clientPackage.remaining_sessions }} sessão(ões)
                </option>
              </select>
            </div>

            <div>
              <label for="professional_id" class="form-label">Profissional preferido</label>
              <select
                id="professional_id"
                v-model="scheduleForm.professional_id"
                class="form-select form-select-lg"
                required
              >
                <option value="" disabled>Selecione um profissional</option>
                <option v-for="professional in professionals" :key="professional.id" :value="professional.id">
                  {{ professional.display_name }} - {{ professional.specialty ?? 'Especialista' }}
                </option>
              </select>
              <div v-if="professionalsLoading" class="form-text">Carregando profissionais...</div>
              <div v-else-if="professionalsError" class="text-danger small">{{ professionalsError }}</div>
            </div>

            <div>
              <label for="scheduled_at" class="form-label">Data e horário desejados</label>
              <input
                id="scheduled_at"
                v-model="scheduleForm.scheduled_at"
                type="datetime-local"
                class="form-control form-control-lg"
                required
              />
              <div class="form-text" v-if="selectedService">
                Sessão estimada em {{ selectedService.duration_min ?? 60 }} minutos.
              </div>
            </div>

            <div>
              <label for="notes" class="form-label">Observações</label>
              <textarea
                id="notes"
                v-model="scheduleForm.notes"
                rows="2"
                class="form-control"
                placeholder="Ex.: Preferência de aroma, restrições ou recados para a clínica"
              ></textarea>
            </div>

            <div v-if="scheduleForm.error" class="alert alert-danger py-2 mb-0">
              {{ scheduleForm.error }}
            </div>
            <div v-if="scheduleForm.success" class="alert alert-success py-2 mb-0">
              {{ scheduleForm.success }}
            </div>

            <button class="btn btn-primary btn-lg" type="submit" :disabled="scheduleForm.submitting">
              <span
                v-if="scheduleForm.submitting"
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              Enviar pedido de agendamento
            </button>
          </form>
        </div>
      </div>
    </section>

    <section>
      <h2 class="h6 fw-semibold mb-3">Meus atendimentos</h2>
      <div v-if="appointmentsLoading" class="card border-0 shadow-sm rounded-4">
        <div class="card-body py-5 text-center text-body-secondary">Carregando atendimentos...</div>
      </div>
      <div v-else-if="appointmentsError" class="alert alert-warning">{{ appointmentsError }}</div>
      <div v-else-if="hasAppointments" class="d-flex flex-column gap-3">
        <div
          v-for="appointment in appointments"
          :key="appointment.id"
          class="card border-0 shadow-sm rounded-4"
        >
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div>
                <h3 class="h6 fw-semibold mb-1">
                  {{ appointment.service?.name ?? 'Sessão agendada' }}
                </h3>
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
    </section>
  </div>
</template>
