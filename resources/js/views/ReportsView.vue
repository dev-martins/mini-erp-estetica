<template>
  <section class="container-fluid px-0 px-md-2">
    <div class="card card-mobile p-3 mb-3">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h2 class="h5 fw-semibold mb-1">Relatórios & desempenho</h2>
          <small class="text-body-secondary">Acompanhe saúde financeira e qualidade dos atendimentos.</small>
        </div>
        <div class="d-flex gap-2">
          <input type="month" v-model="filters.period" class="form-control" />
          <button class="btn btn-outline-secondary" type="button" @click="loadKpis">Atualizar</button>
        </div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-6 col-lg-3" v-for="card in cards" :key="card.title">
        <div class="card card-mobile p-3 h-100">
          <small class="text-body-secondary text-uppercase">{{ card.title }}</small>
          <p class="h4 fw-bold mb-0">{{ card.value }}</p>
          <small :class="card.trendClass">{{ card.trend }}</small>
        </div>
      </div>
    </div>
    <div class="card card-mobile p-3 mt-3">
      <h3 class="h6 fw-semibold mb-3">Fluxo de caixa simplificado</h3>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Categoria</th>
              <th>Valor</th>
              <th>Participação</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="line in dre" :key="line.label">
              <td>{{ line.label }}</td>
              <td>R$ {{ line.amount }}</td>
              <td>{{ line.share }}%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';

const filters = reactive({
  period: new Date().toISOString().slice(0, 7),
});

const kpis = ref({
  revenue: 0,
  cogs: 0,
  expenses: 0,
  margin: 0,
  recurrence: 0,
});

const cards = computed(() => [
  {
    title: 'Receita bruta',
    value: `R$ ${kpis.value.revenue.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`,
    trend: '+12% vs. mês anterior',
    trendClass: 'text-success',
  },
  {
    title: 'Margem líquida',
    value: `${kpis.value.margin}%`,
    trend: '-3 pp vs. mês anterior',
    trendClass: 'text-danger',
  },
  {
    title: 'Clientes recorrentes',
    value: `${kpis.value.recurrence}%`,
    trend: '+5 clientes',
    trendClass: 'text-success',
  },
  {
    title: 'CAC médio',
    value: `R$ ${(kpis.value.expenses / 20).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`,
    trend: 'Estável',
    trendClass: 'text-body-secondary',
  },
]);

const dre = ref([
  { label: 'Receita', amount: '32.500,00', share: 100 },
  { label: 'COGS (kits)', amount: '8.620,00', share: 26.5 },
  { label: 'Despesas fixas', amount: '6.200,00', share: 19.1 },
  { label: 'Comissões', amount: '5.100,00', share: 15.7 },
  { label: 'Lucro líquido', amount: '12.580,00', share: 38.7 },
]);

function loadKpis() {
  // Placeholder - integrar com endpoint /reports/kpis na próxima etapa
  kpis.value = {
    revenue: 32500,
    cogs: 8620,
    expenses: 6200,
    margin: 38.7,
    recurrence: 64,
  };
}

loadKpis();
</script>
