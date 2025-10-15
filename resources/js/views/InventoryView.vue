<template>
  <section class="container-fluid px-0 px-md-2">
    <div class="card card-mobile p-3 mb-3">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h2 class="h5 fw-semibold mb-1">Estoque inteligente</h2>
          <small class="text-body-secondary">Controle lotes, validade e consumo em tempo real.</small>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" type="button">
            Registrar entrada
          </button>
          <button class="btn btn-primary btn-sm" type="button">
            Baixa manual
          </button>
        </div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-12 col-lg-7">
        <div class="card card-mobile p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 fw-semibold mb-0">Itens com atenção</h3>
            <span class="badge text-bg-danger">{{ lowStock.length }} críticos</span>
          </div>
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status" />
          </div>
          <div v-else class="d-flex flex-column gap-2">
            <article
              v-for="product in lowStock"
              :key="product.id"
              class="border rounded-4 px-3 py-3"
            >
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <h4 class="h6 fw-semibold mb-1">{{ product.name }}</h4>
                  <small class="text-body-secondary">{{ product.current_stock }} {{ product.unit }} disponíveis</small>
                </div>
                <span class="badge text-bg-warning">Reposição</span>
              </div>
              <div class="d-flex flex-wrap gap-3 small text-body-secondary">
                <span><i class="fa-regular fa-bell me-1"></i>Ponto mínimo: {{ product.min_stock }}</span>
                <span><i class="fa-regular fa-calendar me-1"></i>{{ product.expiry_control ? 'Monitorar lotes' : 'Sem validade' }}</span>
              </div>
            </article>
            <p v-if="!lowStock.length" class="text-body-secondary text-center py-4 mb-0">Nenhum item crítico 🎉</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-5">
        <div class="card card-mobile p-3 mb-3">
          <h3 class="h6 fw-semibold mb-3">Saldo geral</h3>
          <ul class="list-unstyled d-flex flex-column gap-2 small mb-0">
            <li class="d-flex justify-content-between"><span>Total de itens</span><strong>{{ stats.totalItems }}</strong></li>
            <li class="d-flex justify-content-between"><span>Valor em estoque</span><strong>R$ {{ stats.totalValue }}</strong></li>
            <li class="d-flex justify-content-between"><span>Validade próxima</span><strong>{{ stats.expiring }}</strong></li>
          </ul>
        </div>
        <div class="card card-mobile p-3">
          <h3 class="h6 fw-semibold mb-3">Movimentações recentes</h3>
          <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
            <li
              v-for="movement in movements"
              :key="movement.id"
              class="border rounded-4 px-3 py-2 small d-flex justify-content-between"
            >
              <span>{{ movement.product?.name ?? 'Item' }}</span>
              <span>
                <strong :class="movement.type === 'in' ? 'text-success' : 'text-danger'">
                  {{ movement.type === 'in' ? '+' : '-' }}{{ movement.qty }} {{ movement.product?.unit ?? '' }}
                </strong>
              </span>
            </li>
          </ul>
          <p v-if="!movements.length" class="text-body-secondary text-center py-4 mb-0">Sem movimentações.</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import http from '../services/http';

const products = ref([]);
const movements = ref([]);
const loading = ref(false);

const stats = ref({ totalItems: 0, totalValue: '0,00', expiring: 0 });

const lowStock = computed(() =>
  products.value.filter((product) => Number(product.current_stock ?? 0) <= Number(product.min_stock ?? 0) + 1),
);

async function fetchInventory() {
  loading.value = true;
  try {
    const [productResponse, movementResponse] = await Promise.all([
      http.get('/products', { params: { per_page: 50 } }),
      http.get('/stock-movements', { params: { per_page: 5 } }).catch(() => ({ data: { data: [] } })),
    ]);

    products.value = productResponse.data.data ?? [];
    movements.value = movementResponse.data.data ?? [];

    const totalValue = products.value.reduce(
      (total, product) => total + Number(product.current_stock ?? 0) * Number(product.cost_per_unit ?? 0),
      0,
    );

    stats.value = {
      totalItems: products.value.length,
      totalValue: totalValue.toLocaleString('pt-BR', { minimumFractionDigits: 2 }),
      expiring: products.value.filter((product) => product.expiry_control).length,
    };
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchInventory();
});
</script>
