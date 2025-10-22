<template>
  <section class="container-fluid px-0 px-md-2">
    <div class="card card-mobile p-3 mb-3">
      <div
        class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3"
      >
        <div>
          <h2 class="h5 fw-semibold mb-1">Vendas & POS inteligente</h2>
          <small class="text-body-secondary">
            Concilie pagamentos e acompanhe comissões automaticamente.
          </small>
        </div>
        <div class="d-flex flex-wrap gap-3 text-center justify-content-start justify-content-md-end">
          <div class="px-3 py-2 bg-body-secondary rounded-4">
            <p class="text-body-secondary small mb-0">Total - {{ summary.range_label }}</p>
            <span class="h5 fw-bold">R$ {{ formatCurrency(summary.total) }}</span>
          </div>
          <div class="px-3 py-2 bg-body-secondary rounded-4">
            <p class="text-body-secondary small mb-0">Ticket médio</p>
            <span class="h5 fw-bold">R$ {{ formatCurrency(summary.average_ticket) }}</span>
          </div>
          <div class="px-3 py-2 bg-body-secondary rounded-4">
            <p class="text-body-secondary small mb-0">Comissão prevista</p>
            <span class="h5 fw-bold">R$ {{ formatCurrency(summary.commission) }}</span>
          </div>
        </div>
      </div>
      <div class="mt-3">
        <div class="d-flex flex-wrap gap-2">
          <button
            v-for="option in rangeOptions"
            :key="option.value"
            type="button"
            class="btn btn-sm"
            :class="option.value === range ? 'btn-primary' : 'btn-outline-primary'"
            :aria-pressed="option.value === range"
            :disabled="loading"
            @click="changeRange(option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <div class="card card-mobile p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 fw-semibold mb-0">{{ listTitle }}</h3>
            <button class="btn btn-primary btn-sm" type="button">
              <i class="fa-solid fa-bolt me-2"></i>Checkout rápido
            </button>
          </div>
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status" />
          </div>
          <div v-else>
            <div v-if="sales.length" class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Cliente</th>
                    <th>Canal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th />
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="sale in sales" :key="sale.id">
                    <td>
                      <div class="fw-semibold">{{ sale.client?.full_name ?? 'Venda avulsa' }}</div>
                      <small class="text-body-secondary">{{ formatDateTime(sale.sold_at) }}</small>
                    </td>
                    <td class="text-capitalize">{{ sale.channel ?? '—' }}</td>
                    <td class="fw-semibold">R$ {{ formatCurrency(sale.total_amount) }}</td>
                    <td>
                      <span :class="['badge rounded-pill', statusColor(sale.payment_status)]">
                        {{ statusLabel(sale.payment_status) }}
                      </span>
                    </td>
                    <td class="text-end">
                      <button
                        class="btn btn-outline-secondary btn-sm"
                        type="button"
                        @click="openDetails(sale)"
                      >
                        Detalhes
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-body-secondary text-center py-4 mb-0">
              Nenhuma venda encontrada para o período.
            </p>

            <div
              v-if="showPagination"
              class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-3"
            >
              <small class="text-body-secondary">
                Página {{ pagination.current_page }} de {{ pagination.last_page }}
              </small>
              <nav aria-label="Paginação de vendas">
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item" :class="{ disabled: pagination.current_page <= 1 || loading }">
                    <button
                      class="page-link"
                      type="button"
                      aria-label="Página anterior"
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
                      aria-label="Próxima página"
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
      </div>
      <div class="col-12 col-lg-4">
        <div class="card card-mobile p-3">
          <h3 class="h6 fw-semibold mb-3">Mix de receitas</h3>
          <ul class="list-unstyled d-flex flex-column gap-2 small mb-0">
            <li class="d-flex justify-content-between">
              <span class="text-body-secondary">Serviços</span>
              <span class="fw-semibold">{{ formatPercentage(summary.mix.services) }}</span>
            </li>
            <li class="d-flex justify-content-between">
              <span class="text-body-secondary">Produtos</span>
              <span class="fw-semibold">{{ formatPercentage(summary.mix.products) }}</span>
            </li>
            <li class="d-flex justify-content-between">
              <span class="text-body-secondary">Pacotes</span>
              <span class="fw-semibold">{{ formatPercentage(summary.mix.packages) }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <teleport to="body">
      <div
        v-if="selectedSale"
        class="modal fade show d-block"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        style="background-color: rgba(16, 24, 40, 0.4)"
        @click.self="closeDetails"
      >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-fullscreen-sm-down">
          <div class="modal-content rounded-4">
            <div class="modal-header">
              <h5 class="modal-title">Detalhes da venda</h5>
              <button type="button" class="btn-close" aria-label="Fechar" @click="closeDetails" />
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <p class="mb-1 fw-semibold">{{ selectedSale.client?.full_name ?? 'Venda avulsa' }}</p>
                <small class="text-body-secondary">
                  {{ formatDateTime(selectedSale.sold_at) }} · {{ statusLabel(selectedSale.payment_status) }}
                </small>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                  <div class="border rounded-3 p-3 bg-body-tertiary">
                    <p class="text-body-secondary small mb-1">Subtotal</p>
                    <strong>R$ {{ formatCurrency(selectedSale.subtotal) }}</strong>
                  </div>
                </div>
                <div class="col-12 col-sm-4">
                  <div class="border rounded-3 p-3 bg-body-tertiary">
                    <p class="text-body-secondary small mb-1">Descontos</p>
                    <strong>R$ {{ formatCurrency(selectedSale.discount_total) }}</strong>
                  </div>
                </div>
                <div class="col-12 col-sm-4">
                  <div class="border rounded-3 p-3 bg-body-tertiary">
                    <p class="text-body-secondary small mb-1">Total</p>
                    <strong>R$ {{ formatCurrency(selectedSale.total_amount) }}</strong>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <h6 class="fw-semibold d-flex justify-content-between align-items-center">
                  Itens
                  <span class="badge text-bg-light">{{ selectedSale.items?.length ?? 0 }}</span>
                </h6>
                <ul class="list-group list-group-flush">
                  <li
                    v-if="!selectedSale.items?.length"
                    class="list-group-item px-0 text-body-secondary small"
                  >
                    Nenhum item informado.
                  </li>
                  <li
                    v-for="item in selectedSale.items ?? []"
                    :key="item.id"
                    class="list-group-item px-0 d-flex flex-column flex-sm-row justify-content-between gap-1"
                  >
                    <div>
                      <strong class="d-block">{{ itemTypeLabel(item.item_type) }}</strong>
                      <small class="text-body-secondary">Quantidade: {{ item.qty }}</small>
                    </div>
                    <div class="text-sm-end">
                      <small class="text-body-secondary d-block">
                        Unitário: R$ {{ formatCurrency(item.unit_price) }}
                      </small>
                      <strong>Total: R$ {{ formatCurrency(item.total) }}</strong>
                    </div>
                  </li>
                </ul>
              </div>

              <div>
                <h6 class="fw-semibold d-flex justify-content-between align-items-center">
                  Pagamentos
                  <span class="badge text-bg-light">{{ selectedSale.payments?.length ?? 0 }}</span>
                </h6>
                <ul class="list-group list-group-flush">
                  <li
                    v-if="!selectedSale.payments?.length"
                    class="list-group-item px-0 text-body-secondary small"
                  >
                    Nenhum pagamento registrado.
                  </li>
                  <li
                    v-for="payment in selectedSale.payments ?? []"
                    :key="payment.id"
                    class="list-group-item px-0 d-flex flex-column flex-sm-row justify-content-between gap-1"
                  >
                    <div>
                      <strong class="d-block text-capitalize">{{ payment.method }}</strong>
                      <small class="text-body-secondary">
                        {{ payment.paid_at ? formatDateTime(payment.paid_at) : 'Não informado' }}
                      </small>
                    </div>
                    <strong>R$ {{ formatCurrency(payment.amount) }}</strong>
                  </li>
                </ul>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeDetails">Fechar</button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import http from '../services/http';

const perPage = 8;

const rangeOptions = [
  { value: 'recent', label: 'Recentes' },
  { value: 'day', label: 'Hoje' },
  { value: 'week', label: 'Semana' },
  { value: 'month', label: 'Mês' },
  { value: 'year', label: 'Ano' },
];

const rangeLabels = {
  recent: 'Recentes',
  day: 'Hoje',
  week: 'Semana',
  month: 'Mês',
  year: 'Ano',
};

const sales = ref([]);
const loading = ref(false);
const range = ref('recent');
const page = ref(1);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});
const summary = ref({
  total: 0,
  average_ticket: 0,
  commission: 0,
  range_label: rangeLabels.recent,
  mix: {
    services: 0,
    products: 0,
    packages: 0,
  },
  period: {
    from: null,
    to: null,
  },
});
const selectedSale = ref(null);

const listTitle = computed(() => (range.value === 'recent' ? 'Vendas recentes' : 'Vendas filtradas'));
const showPagination = computed(
  () => range.value !== 'recent' && (pagination.value?.last_page ?? 1) > 1,
);

const pageNumbers = computed(() => {
  const total = pagination.value?.last_page ?? 1;
  const current = pagination.value?.current_page ?? 1;

  if (total <= 1) {
    return [1];
  }

  const numbers = new Set([1, total, current - 1, current, current + 1]);
  return Array.from(numbers)
    .filter((value) => value >= 1 && value <= total)
    .sort((a, b) => a - b);
});

function rangeLabelFor(key) {
  return rangeLabels[key] ?? 'Período selecionado';
}

function formatCurrency(value) {
  return Number(value ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

function formatDateTime(value) {
  if (!value) {
    return '—';
  }

  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value));
}

function statusLabel(status) {
  return {
    paid: 'Pago',
    partial: 'Parcial',
    pending: 'Pendente',
    refunded: 'Estornado',
  }[status] ?? status;
}

function statusColor(status) {
  return {
    paid: 'text-bg-success',
    partial: 'text-bg-warning',
    pending: 'text-bg-secondary',
    refunded: 'text-bg-danger',
  }[status] ?? 'text-bg-secondary';
}

function itemTypeLabel(type) {
  return {
    service: 'Serviço',
    product: 'Produto',
    package: 'Pacote',
  }[type] ?? type;
}

function formatPercentage(value) {
  return `${Number(value ?? 0).toFixed(1)}%`;
}

async function fetchSales() {
  loading.value = true;

  try {
    const params = {
      per_page: perPage,
      page: page.value,
      range: range.value,
    };

    const response = await http.get('/sales', { params });
    const payload = response.data ?? {};

    sales.value = payload.data ?? [];
    pagination.value = {
      current_page: payload.meta?.current_page ?? 1,
      last_page: payload.meta?.last_page ?? 1,
      total: payload.meta?.total ?? sales.value.length,
    };

    const summaryPayload = payload.summary ?? {};

    summary.value = {
      total: summaryPayload.total ?? 0,
      average_ticket: summaryPayload.average_ticket ?? 0,
      commission: summaryPayload.commission_estimated ?? 0,
      range_label: summaryPayload.range_label ?? rangeLabelFor(range.value),
      mix: {
        services: summaryPayload.mix?.services ?? 0,
        products: summaryPayload.mix?.products ?? 0,
        packages: summaryPayload.mix?.packages ?? 0,
      },
      period: summaryPayload.period ?? {
        from: null,
        to: null,
      },
    };
  } catch (error) {
    console.error('Falha ao carregar vendas', error);
    sales.value = [];
  } finally {
    loading.value = false;
  }
}

function changeRange(value) {
  if (range.value === value) {
    return;
  }

  range.value = value;
  page.value = 1;
  fetchSales();
}

function changePage(target) {
  const lastPage = pagination.value?.last_page ?? 1;

  if (target < 1 || target > lastPage || target === page.value) {
    return;
  }

  page.value = target;
  fetchSales();
}

function openDetails(sale) {
  selectedSale.value = sale;
}

function closeDetails() {
  selectedSale.value = null;
}

onMounted(() => {
  fetchSales();
});
</script>

<style scoped>
.card-mobile {
  border-radius: 1rem;
}

.list-group-item {
  background-color: transparent;
}

@media (max-width: 575.98px) {
  .pagination {
    justify-content: center;
  }
}
</style>
