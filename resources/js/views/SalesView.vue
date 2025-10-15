<template>
  <section class="container-fluid px-0 px-md-2">
    <div class="card card-mobile p-3 mb-3">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div>
          <h2 class="h5 fw-semibold mb-1">Vendas & POS inteligente</h2>
          <small class="text-body-secondary">Concilie pagamentos e acompanhe comissões automaticamente.</small>
        </div>
        <div class="d-flex flex-wrap gap-3 text-center">
          <div class="px-3 py-2 bg-body-secondary rounded-4">
            <p class="text-body-secondary small mb-0">Hoje</p>
            <span class="h5 fw-bold">R$ {{ kpis.today }}</span>
          </div>
          <div class="px-3 py-2 bg-body-secondary rounded-4">
            <p class="text-body-secondary small mb-0">Ticket médio</p>
            <span class="h5 fw-bold">R$ {{ kpis.ticket }}</span>
          </div>
          <div class="px-3 py-2 bg-body-secondary rounded-4">
            <p class="text-body-secondary small mb-0">Comissão prevista</p>
            <span class="h5 fw-bold">R$ {{ kpis.commission }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <div class="card card-mobile p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 fw-semibold mb-0">Vendas recentes</h3>
            <button class="btn btn-primary btn-sm" type="button">
              <i class="fa-solid fa-bolt me-2"></i>Checkout rápido
            </button>
          </div>
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status" />
          </div>
          <div v-else class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>Canal</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in sales" :key="sale.id">
                  <td>
                    <div class="fw-semibold">{{ sale.client?.full_name ?? 'Venda avulsa' }}</div>
                    <small class="text-body-secondary">{{ formatDateTime(sale.sold_at) }}</small>
                  </td>
                  <td class="text-capitalize">{{ sale.channel }}</td>
                  <td class="fw-semibold">R$ {{ formatCurrency(sale.total_amount) }}</td>
                  <td>
                    <span :class="['badge rounded-pill', statusColor(sale.payment_status)]">
                      {{ statusLabel(sale.payment_status) }}
                    </span>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-outline-secondary btn-sm" type="button">Detalhes</button>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="!sales.length" class="text-body-secondary text-center py-4 mb-0">Nenhuma venda registrada ainda.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="card card-mobile p-3">
          <h3 class="h6 fw-semibold mb-3">Mix de receitas</h3>
          <ul class="list-unstyled d-flex flex-column gap-2 small mb-0">
            <li class="d-flex justify-content-between">
              <span class="text-body-secondary">Serviços</span>
              <span class="fw-semibold">{{ mix.services }}%</span>
            </li>
            <li class="d-flex justify-content-between">
              <span class="text-body-secondary">Produtos</span>
              <span class="fw-semibold">{{ mix.products }}%</span>
            </li>
            <li class="d-flex justify-content-between">
              <span class="text-body-secondary">Pacotes</span>
              <span class="fw-semibold">{{ mix.packages }}%</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import http from '../services/http';

const sales = ref([]);
const loading = ref(false);

const kpis = ref({ today: '0,00', ticket: '0,00', commission: '0,00' });
const mix = ref({ services: 100, products: 0, packages: 0 });

function formatCurrency(value) {
  return Number(value ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

function formatDateTime(value) {
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

async function fetchSales() {
  loading.value = true;
  try {
    const { data } = await http.get('/sales', { params: { per_page: 10 } });
    sales.value = data.data ?? [];
    const totalToday = sales.value
      .filter((sale) => new Date(sale.sold_at).toDateString() === new Date().toDateString())
      .reduce((sum, sale) => sum + Number(sale.total_amount ?? 0), 0);
    const total = sales.value.reduce((sum, sale) => sum + Number(sale.total_amount ?? 0), 0);
    const count = sales.value.length || 1;
    kpis.value.today = formatCurrency(totalToday);
    kpis.value.ticket = formatCurrency(total / count);
    kpis.value.commission = formatCurrency(total * 0.25);
    mix.value = {
      services: 70,
      products: 20,
      packages: 10,
    };
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchSales();
});
</script>
