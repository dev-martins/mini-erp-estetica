<template>
  <teleport to="body">
    <div
      v-if="show"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(16, 24, 40, 0.4)"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <form @submit.prevent="handleSubmit" novalidate>
            <div class="modal-header">
              <h5 class="modal-title">Novo agendamento</h5>
              <button type="button" class="btn-close" aria-label="Fechar" @click="close" />
            </div>

            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label text-body-secondary small">Cliente</label>
                  <select v-model="form.client_id" class="form-select" required>
                    <option value="" disabled>Selecione o cliente</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.full_name }} - {{ client.phone }}
                    </option>
                  </select>
                  <small v-if="errors.client_id" class="text-danger">{{ errors.client_id }}</small>
                </div>

                <div class="col-12">
                  <label class="form-label text-body-secondary small">Servico</label>
                  <select v-model="form.service_id" class="form-select" required>
                    <option value="" disabled>Selecione o servico</option>
                    <option v-for="service in services" :key="service.id" :value="service.id">
                      {{ service.name }}
                    </option>
                  </select>
                  <small v-if="errors.service_id" class="text-danger">{{ errors.service_id }}</small>
                </div>

                <div class="col-12">
                  <label class="form-label text-body-secondary small">Pacote</label>
                  <select
                    v-model="form.client_package_id"
                    class="form-select"
                    :disabled="!packages.length || loadingPackages"
                    required
                  >
                    <option value="" disabled>
                      {{
                        loadingPackages
                          ? 'Carregando pacotes...'
                          : packages.length
                            ? 'Selecione o pacote'
                            : 'Nenhum pacote disponivel'
                      }}
                    </option>
                    <option v-for="pack in packages" :key="pack.id" :value="pack.id">
                      {{ pack.package.name }} - {{ pack.remaining_sessions }} restante(s)
                      <template v-if="pack.expiry_at">
                        - vence em {{ formatDate(pack.expiry_at) }}
                      </template>
                    </option>
                  </select>
                  <div v-if="!loadingPackages && !packages.length" class="form-text text-danger">
                    O cliente precisa comprar um pacote valido para este servico antes de agendar.
                  </div>
                  <div v-if="minIntervalHours" class="form-text text-body-secondary">
                    Intervalo minimo entre sessoes: {{ minIntervalHours }} hora(s).
                  </div>
                  <small v-if="errors.client_package_id" class="text-danger">{{ errors.client_package_id }}</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label text-body-secondary small">Profissional</label>
                  <select v-model="form.professional_id" class="form-select" required>
                    <option value="" disabled>Selecione</option>
                    <option v-for="pro in professionals" :key="pro.id" :value="pro.id">
                      {{ pro.display_name }}
                      <span v-if="pro.specialty"> - {{ pro.specialty }}</span>
                    </option>
                  </select>
                  <small v-if="errors.professional_id" class="text-danger">{{ errors.professional_id }}</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label text-body-secondary small">Sala (opcional)</label>
                  <select v-model="form.room_id" class="form-select">
                    <option value="">Sem sala vinculada</option>
                    <option v-for="room in rooms" :key="room.id" :value="room.id">
                      {{ room.name }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label text-body-secondary small">Equipamento (opcional)</label>
                  <select v-model="form.equipment_id" class="form-select">
                    <option value="">Nenhum equipamento</option>
                    <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">
                      {{ equipment.name }}
                    </option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label text-body-secondary small">Data e hora</label>
                  <input
                    v-model="form.scheduled_at"
                    type="datetime-local"
                    class="form-control"
                    required
                  />
                  <small v-if="errors.scheduled_at" class="text-danger">{{ errors.scheduled_at }}</small>
                  <small v-else-if="professionalConflictMessage" class="text-danger">
                    {{ professionalConflictMessage }}
                  </small>
                  <small v-else-if="intervalConflictMessage" class="text-danger">
                    {{ intervalConflictMessage }}
                  </small>
                </div>

                <div class="col-md-6">
                  <label class="form-label text-body-secondary small">Duracao (min)</label>
                  <input
                    v-model.number="form.duration_min"
                    type="number"
                    min="15"
                    step="5"
                    class="form-control"
                    required
                  />
                </div>

                <div class="col-12">
                  <label class="form-label text-body-secondary small">Observacoes</label>
                  <textarea
                    v-model="form.notes"
                    rows="3"
                    class="form-control"
                    placeholder="Protocolos, restricoes ou combinados."
                  />
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <span v-if="errors.general" class="text-danger small">{{ errors.general }}</span>
                <span v-else class="text-body-secondary small">
                  O sistema verifica estoque, saldo do pacote e conflitos de agenda automaticamente.
                </span>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="close" :disabled="loading">
                  Cancelar
                </button>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="!packages.length || loading || hasBlockingConflict"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" />
                  Agendar
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import http from '../../services/http';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  existingAppointments: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close', 'created']);

const clients = ref([]);
const professionals = ref([]);
const services = ref([]);
const rooms = ref([]);
const equipments = ref([]);
const packages = ref([]);

const loading = ref(false);
const loadingPackages = ref(false);
const initialized = ref(false);

const errors = reactive({
  client_id: null,
  client_package_id: null,
  professional_id: null,
  service_id: null,
  scheduled_at: null,
  general: null,
});

const form = reactive({
  client_id: '',
  client_package_id: '',
  service_id: '',
  professional_id: '',
  room_id: '',
  equipment_id: '',
  scheduled_at: defaultDateTime(),
  duration_min: 60,
  notes: '',
});

const show = computed(() => props.show);
const existingAppointments = computed(() => props.existingAppointments ?? []);

watch(
  () => show.value,
  (visible) => {
    if (visible && !initialized.value) {
      bootstrapData();
    }
    if (!visible) {
      resetForm();
    }
  },
);

watch(
  () => [form.client_id, form.service_id],
  async ([clientId, serviceId]) => {
    if (clientId && serviceId) {
      await fetchPackages(clientId, serviceId);
    } else {
      packages.value = [];
      form.client_package_id = '';
    }
  },
);

const selectedPackage = computed(() =>
  packages.value.find((pack) => Number(pack.id) === Number(form.client_package_id)) ?? null,
);

const minIntervalHours = computed(() => {
  const pack = selectedPackage.value;
  if (!pack) {
    return 0;
  }

  const pkgInterval = Number(pack.package?.min_interval_hours ?? 0);
  const serviceInterval = Number(pack.package?.service_min_interval_hours ?? 0);

  return pkgInterval || serviceInterval || 0;
});

const professionalConflictMessage = computed(() => {
  if (!form.professional_id || !form.scheduled_at) {
    return null;
  }

  const start = parseLocalDateTime(form.scheduled_at);
  if (!start) {
    return null;
  }

  const duration = Number(form.duration_min || 0);
  const end = new Date(start.getTime() + duration * 60000);
  const professionalId = Number(form.professional_id);

  const conflictExists = existingAppointments.value.some((appointment) => {
    const appointmentProfessionalId =
      Number(appointment.professional?.id ?? appointment.professional_id ?? 0);

    if (
      appointmentProfessionalId !== professionalId ||
      !['pending', 'confirmed'].includes(appointment.status) ||
      !appointment.scheduled_at
    ) {
      return false;
    }

    const appointmentStart = new Date(appointment.scheduled_at);
    const appointmentEnd = appointment.ended_at
      ? new Date(appointment.ended_at)
      : new Date(
          appointmentStart.getTime() +
            Number(appointment.duration_min ?? form.duration_min ?? 60) * 60000,
        );

    return appointmentStart < end && appointmentEnd > start;
  });

  return conflictExists
    ? 'Horario indisponivel: o profissional ja possui atendimento neste periodo.'
    : null;
});

const intervalConflictMessage = computed(() => {
  if (!form.client_id || !form.service_id || !form.scheduled_at || !minIntervalHours.value) {
    return null;
  }

  const start = parseLocalDateTime(form.scheduled_at);
  if (!start) {
    return null;
  }

  const clientId = Number(form.client_id);
  const serviceId = Number(form.service_id);
  const minIntervalMs = minIntervalHours.value * 60 * 60 * 1000;

  const conflictExists = existingAppointments.value.some((appointment) => {
    const appointmentClientId = Number(appointment.client?.id ?? appointment.client_id ?? 0);
    const appointmentServiceId = Number(appointment.service?.id ?? appointment.service_id ?? 0);

    if (
      appointmentClientId !== clientId ||
      appointmentServiceId !== serviceId ||
      !['pending', 'confirmed', 'completed'].includes(appointment.status) ||
      !appointment.scheduled_at
    ) {
      return false;
    }

    const appointmentStart = new Date(appointment.scheduled_at);
    const diffMs = Math.abs(appointmentStart.getTime() - start.getTime());

    return diffMs < minIntervalMs;
  });

  return conflictExists
    ? `Respeite o intervalo minimo de ${minIntervalHours.value} hora(s) entre as sessoes deste tratamento.`
    : null;
});

const hasBlockingConflict = computed(
  () => Boolean(professionalConflictMessage.value || intervalConflictMessage.value),
);

watch(hasBlockingConflict, (blocked) => {
  if (!blocked && errors.scheduled_at) {
    if (
      errors.scheduled_at === professionalConflictMessage.value ||
      errors.scheduled_at === intervalConflictMessage.value
    ) {
      errors.scheduled_at = null;
    }
  }
});

async function bootstrapData() {
  try {
    initialized.value = true;
    const [clientsRes, servicesRes, professionalsRes, roomsRes, equipmentsRes] = await Promise.all([
      http.get('/clients', { params: { per_page: 100 } }),
      http.get('/services', { params: { per_page: 100 } }),
      http.get('/professionals'),
      http.get('/rooms'),
      http.get('/equipments'),
    ]);

    clients.value = clientsRes.data?.data ?? [];
    services.value = servicesRes.data?.data ?? [];
    professionals.value = professionalsRes.data?.data ?? [];
    rooms.value = roomsRes.data?.data ?? [];
    equipments.value = equipmentsRes.data?.data ?? [];

    if (!form.professional_id && professionals.value.length) {
      form.professional_id = professionals.value[0].id;
    }
  } catch (error) {
    errors.general = error.response?.data?.message ?? 'Falha ao carregar dados base.';
  }
}

async function fetchPackages(clientId, serviceId) {
  loadingPackages.value = true;
  form.client_package_id = '';
  errors.client_package_id = null;

  try {
    const { data } = await http.get(`/clients/${clientId}/packages`, {
      params: { service_id: serviceId },
    });
    packages.value = data?.data ?? [];
  } catch (error) {
    packages.value = [];
    errors.client_package_id =
      error.response?.data?.errors?.client_package_id?.[0] ??
      'Nao foi possivel listar os pacotes disponiveis.';
  } finally {
    loadingPackages.value = false;
  }
}

function resetErrors() {
  Object.keys(errors).forEach((key) => {
    errors[key] = null;
  });
}

function resetForm() {
  resetErrors();
  form.client_id = '';
  form.client_package_id = '';
  form.service_id = '';
  form.professional_id = professionals.value[0]?.id ?? '';
  form.room_id = '';
  form.equipment_id = '';
  form.scheduled_at = defaultDateTime();
  form.duration_min = 60;
  form.notes = '';
  packages.value = [];
}

function parseLocalDateTime(value) {
  if (!value) {
    return null;
  }

  const [datePart, timePart] = value.split('T');
  if (!datePart || !timePart) {
    return null;
  }

  const [year, month, day] = datePart.split('-').map(Number);
  const [hour, minute] = timePart.split(':').map(Number);

  if ([year, month, day, hour, minute].some((n) => Number.isNaN(n))) {
    return null;
  }

  return new Date(year, month - 1, day, hour, minute, 0);
}

function defaultDateTime() {
  const now = new Date();
  now.setSeconds(0, 0);
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  return now.toISOString().slice(0, 16);
}

function toApiDateTime(datetimeLocal) {
  const date = parseLocalDateTime(datetimeLocal);
  if (!date) {
    return null;
  }

  const pad = (value) => value.toString().padStart(2, '0');
  const year = date.getFullYear();
  const month = pad(date.getMonth() + 1);
  const day = pad(date.getDate());
  const hour = pad(date.getHours());
  const minute = pad(date.getMinutes());

  const offsetMinutes = -date.getTimezoneOffset();
  const sign = offsetMinutes >= 0 ? '+' : '-';
  const absOffset = Math.abs(offsetMinutes);
  const offsetHours = pad(Math.floor(absOffset / 60));
  const offsetMins = pad(absOffset % 60);

  return `${year}-${month}-${day}T${hour}:${minute}:00${sign}${offsetHours}:${offsetMins}`;
}

function formatDate(iso) {
  return new Intl.DateTimeFormat('pt-BR').format(new Date(iso));
}

function close() {
  emit('close');
}

async function handleSubmit() {
  resetErrors();

  if (hasBlockingConflict.value) {
    errors.scheduled_at = professionalConflictMessage.value ?? intervalConflictMessage.value;
    return;
  }

  const payload = {
    client_id: Number(form.client_id),
    client_package_id: Number(form.client_package_id),
    professional_id: Number(form.professional_id),
    room_id: form.room_id || null,
    equipment_id: form.equipment_id || null,
    service_id: Number(form.service_id),
    scheduled_at: toApiDateTime(form.scheduled_at),
    duration_min: Number(form.duration_min),
    notes: form.notes || null,
    status: 'pending',
  };

  loading.value = true;

  try {
    const { data } = await http.post('/appointments', payload);
    emit('created', data.data ?? data);
  } catch (error) {
    const responseErrors = error.response?.data?.errors;
    if (responseErrors) {
      Object.entries(responseErrors).forEach(([field, messages]) => {
        errors[field] = Array.isArray(messages) ? messages[0] : messages;
      });
    } else {
      errors.general = error.response?.data?.message ?? 'Falha ao salvar agendamento.';
    }
    return;
  } finally {
    loading.value = false;
  }

  close();
}
</script>
