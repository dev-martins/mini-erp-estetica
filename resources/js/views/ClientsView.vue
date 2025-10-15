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
            <div class="spinner-border text-primary" role="status" />
          </div>
          <div v-else class="d-flex flex-column gap-2">
            <article
              v-for="client in clients"
              :key="client.id"
              class="border rounded-4 px-3 py-3 d-flex flex-column gap-2"
            >
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <h4 class="h6 fw-semibold mb-0">{{ client.full_name }}</h4>
                  <small class="text-body-secondary">{{ client.phone }} · {{ client.email ?? 'sem e-mail' }}</small>
                </div>
                <span class="badge text-bg-light text-body-secondary">{{ client.source ?? 'Orgânico' }}</span>
              </div>
              <div class="d-flex flex-wrap gap-3 small text-body-secondary">
                <span><i class="fa-regular fa-calendar-check me-1"></i>{{ formatDate(client.last_appointment_at) }}</span>
                <span><i class="fa-solid fa-trophy me-1"></i>{{ client.tags?.join(', ') ?? 'Sem tags' }}</span>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm flex-fill" type="button">
                  <i class="fa-brands fa-whatsapp me-1"></i>Enviar WhatsApp
                </button>
                <button class="btn btn-outline-secondary btn-sm" type="button">
                  Detalhes
                </button>
              </div>
            </article>
            <p v-if="!clients.length" class="text-body-secondary text-center py-4 mb-0">Nenhum cliente encontrado.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="card card-mobile p-3 mb-3">
          <h3 class="h6 fw-semibold mb-3">Resumo rápido</h3>
          <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
            <li class="d-flex justify-content-between"><span>Clientes ativos</span><strong>{{ stats.total }}</strong></li>
            <li class="d-flex justify-content-between"><span>Reativações 30d</span><strong>{{ stats.reengagement }}</strong></li>
            <li class="d-flex justify-content-between"><span>Opt-in marketing</span><strong>{{ stats.optIn }}%</strong></li>
          </ul>
        </div>
        <div class="card card-mobile p-3">
          <h3 class="h6 fw-semibold mb-3">Listas inteligentes</h3>
          <div class="d-flex flex-column gap-2">
            <button class="btn btn-light border text-start" type="button">
              <div class="fw-semibold">Sem retorno há 30 dias</div>
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
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import http from '../services/http';

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

function formatDate(value) {
  if (!value) {
    return 'Sem sessões';
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
      ((clients.value.filter((client) => client.consent_marketing).length || 0) /
        (clients.value.length || 1)) *
        100,
    );
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchClients();
});
</script>
