# AGENTS.md — Mini‑ERP de Estética (Mobile‑First)

> Documento de orquestração para agentes (Codex) e humanos. Define visão, escopo, requisitos, arquitetura, modelo de dados, prompts, critérios de aceite e plano de entrega para um mini‑ERP de estética multi‑profissional. **Foco mobile‑first**.

---

## 0) TL;DR

* Construir um **mini‑ERP para clínicas/salas compartilhadas de beleza** (esteticista, manicure, bronze, cabeleireira), com **agenda**, **vendas/POS**, **estoque de insumos**, **CRM/fidelidade**, **campanhas**, **acompanhamento de resultados** e **finanças** (custos fixos/variáveis, comissões, DRE simples).
* **Stack:** Laravel 11/12 + MySQL 8 + Vue 3 (Vite) + Bootstrap 5/AdminLTE 4. **PWA** e **mobile‑first**.
* **Segurança/LGPD**: consentimento, termos, perfis/permissões, auditoria básica.
* **MVP em 4 semanas**, evoluindo em sprints.

---

## 1) Visão & Objetivos

* **Captação:** leads por canal (Instagram/WhatsApp/indicação), funil simples, UTMs.
* **Conversão & Retenção:** agendamento rápido, lembretes, confirmação 1‑clique, no‑show controlado.
* **Fidelização:** pontos, cupons, pacotes, reativação automática.
* **Qualidade:** fotos antes/depois (com consentimento), medidas, NPS.
* **Financeiro:** custos por sessão (insumos), comissões, fluxo de caixa, DRE.
* **Operação multi‑profissional/sala/equipamento** sem canibalizar clientela (cross‑referral).

### Indicadores‑chave (KPIs)

* Ocupação (%), No‑show rate, Ticket médio, LTV, CAC por canal, Payback de equipamentos, Reativação 60/90d, COGS por sessão.

---

## 2) Requisitos (funcionais e não‑funcionais)

### Funcionais principais

1. **Agenda** por profissional/sala/equipamento; bloqueios e lista de espera.
2. **CRM**: leads → qualificado → agendado → cliente → recompra.
3. **Vendas/POS**: serviços, pacotes, produtos; pagamentos (PIX, cartão, dinheiro).
4. **Estoque**: produtos/insumos, lotes, validade, ponto de reposição; baixa por sessão via *kit* do serviço.
5. **Marketing**: UTMs, cupons, indicações, disparos segmentados (templates WhatsApp/e-mail).
6. **Resultados**: fotos antes/depois, medidas; plano de tratamento e NPS pós‑sessão.
7. **Financeiro**: comissões, despesas fixas (ex.: R$300 de sala/luz), DRE simples, fluxo de caixa.
8. **LGPD**: consentimento, termos PDF, auditoria de acessos; perfis e permissões.

### Não‑funcionais

* **Mobile‑first** (PWA opcional), acessível (WCAG AA), responsivo, rápido (Core Web Vitals).
* **DDD** leve (Controller → Service → Repository), DTOs, eventos/filas.
* Testes (PHPUnit, Pest opcional), PHPCS, PHPStan, Parallel Lint, Composer Audit.
* Logs por domínio e métricas básicas de performance (tabela `query_metrics`, opcional).

---

## 3) Arquitetura & Padrões

* **Laravel**: domínios `Appointments`, `Clients`, `Sales`, `Inventory`, `Marketing`, `Loyalty`, `Finance`, `Compliance`.
* **Vue 3** (composition API) + **Bootstrap 5**/**AdminLTE 4**:

  * Rotas: `/agenda`, `/clientes`, `/vendas`, `/estoque`, `/relatorios`, `/marketing`, `/config`.
  * Componentes: `CalendarView (FullCalendar)`, `ClientQuickCreate`, `POSCheckout`, `BeforeAfterUploader`, `PackageSelector`.
* **Eventos**: `AppointmentConfirmed`, `SaleCompleted`, `StockBelowMin`, `PackageConsumed`.
* **Filas/Jobs**: lembretes, baixa de estoque por sessão, cálculo de comissão, disparos de campanha.
* **Armazenamento**: fotos em disco local (abstraído para S3/Azure futuramente).

### Estrutura de pastas sugerida

```
app/
  Domain/
    Appointments/ { Controllers, DTOs, Services, Repositories, Models }
    Clients/      { ... }
    Sales/        { ... }
    Inventory/    { ... }
    Marketing/    { ... }
    Loyalty/      { ... }
    Finance/      { ... }
    Compliance/   { ... }
resources/
  js/ (Vue, stores, components)
  views/
public/
database/ { migrations, seeders, factories }
```

---

## 4) Modelo de Dados (ERD + PlantUML corrigido)

### Visão geral das entidades

* **users/professionals** (multi‑perfil), **clients/leads**, **appointments** (com sala/equipamento/serviço), **services/service_kits/kit_items**, **products/product_batches/stock_movements**, **sales/sale_items/payments**, **packages/client_packages**, **loyalty_rules/loyalty_points**, **marketing_campaigns/campaign_events**, **expenses**, **commissions**, **anamneses/referrals**, **session_photos/session_measures/session_items**.

### PlantUML (corrigido)

> Copie/cole no PlantUML. Não use `;` dentro de `entity`. Cada atributo em **uma linha**.

```plantuml
@startuml
hide circle
skinparam linetype ortho
skinparam entity {
  BackgroundColor #F8FAFF
  BorderColor #1B2735
  FontColor #0D111D
}

title Mini-ERP Estética (MVP→Escalável)

entity "users" as users {
  *id : bigint
  --
  name : varchar
  email : varchar
  phone : varchar
  role : varchar
  password_hash : varchar
}

entity "professionals" as professionals {
  *id : bigint
  --
  user_id : bigint <<FK>>
  display_name : varchar
  specialty : varchar
  commission_type : enum
  commission_value : decimal
}

entity "rooms" as rooms { *id : bigint \n name : varchar \n notes : text }
entity "equipments" as equipments { *id : bigint \n name : varchar \n serial : varchar \n maint_cycle_days : int }

entity "services" as services {
  *id : bigint
  --
  name : varchar
  category : varchar
  duration_min : int
  list_price : decimal
  kit_id : bigint <<FK>>
  active : bool
}

entity "service_kits" as service_kits { *id : bigint \n name : varchar \n notes : text }
entity "kit_items" as kit_items { *id : bigint \n kit_id : bigint <<FK>> \n product_id : bigint <<FK>> \n qty_per_session : decimal }

entity "products" as products { *id : bigint \n name : varchar \n unit : varchar \n cost_per_unit : decimal \n min_stock : decimal \n expiry_control : bool }
entity "product_batches" as product_batches { *id : bigint \n product_id : bigint <<FK>> \n batch_code : varchar \n qty : decimal \n expiry_date : date \n unit_cost : decimal }
entity "suppliers" as suppliers { *id : bigint \n name : varchar \n phone : varchar \n email : varchar }
entity "stock_movements" as stock_movements { *id : bigint \n product_id : bigint <<FK>> \n batch_id : bigint <<FK>> \n type : enum \n qty : decimal \n unit_cost : decimal \n reason : varchar \n created_at : datetime }

entity "clients" as clients {
  *id : bigint
  --
  full_name : varchar
  phone : varchar
  email : varchar
  birthdate : date
  instagram : varchar
  consent_marketing : bool
  created_at : datetime
}

entity "leads" as leads { *id : bigint \n name : varchar \n phone : varchar \n source : varchar \n utm_source : varchar \n utm_campaign : varchar \n status : varchar }
entity "anamneses" as anamneses { *id : bigint \n client_id : bigint <<FK>> \n form_json : text \n signed_at : datetime \n signer_name : varchar }
entity "referrals" as referrals { *id : bigint \n client_id : bigint <<FK>> \n referred_by_client_id : bigint <<FK>> \n channel : varchar }

entity "appointments" as appointments {
  *id : bigint
  --
  client_id : bigint <<FK>>
  professional_id : bigint <<FK>>
  room_id : bigint <<FK>>
  equipment_id : bigint <<FK>>
  service_id : bigint <<FK>>
  scheduled_at : datetime
  duration_min : int
  status : enum
  source : varchar
  notes : text
  started_at : datetime
  ended_at : datetime
}

entity "session_photos" as session_photos { *id : bigint \n appointment_id : bigint <<FK>> \n path : varchar \n type : enum \n consent_id : bigint }
entity "session_measures" as session_measures { *id : bigint \n appointment_id : bigint <<FK>> \n metric : varchar \n value : decimal \n unit : varchar }
entity "session_items" as session_items { *id : bigint \n appointment_id : bigint <<FK>> \n product_id : bigint <<FK>> \n quantity_used : decimal \n batch_id : bigint <<FK>> }

entity "sales" as sales { *id : bigint \n client_id : bigint <<FK>> \n sold_at : datetime \n total_amount : decimal \n channel : varchar \n source : varchar \n payment_status : varchar \n notes : text }
entity "sale_items" as sale_items { *id : bigint \n sale_id : bigint <<FK>> \n item_type : enum \n item_id : bigint \n qty : decimal \n unit_price : decimal \n discount : decimal \n total : decimal }
entity "payments" as payments { *id : bigint \n sale_id : bigint <<FK>> \n method : enum \n amount : decimal \n paid_at : datetime \n tx_ref : varchar }

entity "packages" as packages { *id : bigint \n name : varchar \n sessions_count : int \n price : decimal \n service_id : bigint <<FK>> \n expiry_days : int }
entity "client_packages" as client_packages { *id : bigint \n client_id : bigint <<FK>> \n package_id : bigint <<FK>> \n purchased_at : datetime \n remaining_sessions : int \n expiry_at : datetime }

entity "loyalty_rules" as loyalty_rules { *id : bigint \n rule_type : enum \n value : decimal \n points : int \n active : bool }
entity "loyalty_points" as loyalty_points { *id : bigint \n client_id : bigint <<FK>> \n points : int \n reason : varchar \n ref_id : bigint \n created_at : datetime }
entity "coupons" as coupons { *id : bigint \n code : varchar \n type : enum \n value : decimal \n starts_at : datetime \n ends_at : datetime \n max_uses : int \n used_count : int }
entity "marketing_campaigns" as marketing_campaigns { *id : bigint \n name : varchar \n channel : varchar \n budget : decimal \n starts_at : datetime \n ends_at : datetime \n utm_source : varchar \n utm_campaign : varchar }
entity "campaign_events" as campaign_events { *id : bigint \n campaign_id : bigint <<FK>> \n client_id : bigint <<FK>> \n event : varchar \n at : datetime \n meta_json : text }
entity "expenses" as expenses { *id : bigint \n category : enum \n amount : decimal \n paid_at : datetime \n notes : text }
entity "commissions" as commissions { *id : bigint \n professional_id : bigint <<FK>> \n sale_id : bigint <<FK>> \n amount : decimal \n calculated_at : datetime }

users ||--o{ professionals
clients ||--o{ appointments
professionals ||--o{ appointments
rooms ||--o{ appointments
equipments ||--o{ appointments
services ||--o{ appointments
appointments ||--o{ session_photos
appointments ||--o{ session_measures
appointments ||--o{ session_items
products ||--o{ session_items
product_batches ||--o{ session_items
services ||--o{ service_kits
service_kits ||--o{ kit_items
products ||--o{ kit_items
products ||--o{ product_batches
suppliers ||--o{ stock_movements
clients ||--o{ sales
sales ||--o{ sale_items
sales ||--o{ payments
packages ||--o{ client_packages
clients ||--o{ client_packages
clients ||--o{ loyalty_points
loyalty_rules ||--o{ loyalty_points
clients ||--o{ referrals
marketing_campaigns ||--o{ campaign_events
sales ||--o{ commissions
@enduml
```

---

## 5) API (esqueleto)

**Auth:** Laravel Sanctum.

### Endpoints (exemplos)

* `POST /api/clients` (create) / `GET /api/clients` (paginate, filtros `search`, `no_return_since`)
* `POST /api/appointments` (cria e agenda) / `PATCH /api/appointments/{id}/confirm|cancel|done|no-show`
* `POST /api/sales` (checkout) + itens + pagamentos
* `POST /api/stock/entries` | `POST /api/stock/consume` (por atendimento)
* `GET /api/reports/kpis?from=YYYY-MM&to=YYYY-MM`

**Padrões**: Request/Response DTOs; erros 4xx/5xx uniformes; paginação `meta` + `links`.

---

## 6) Frontend — **Mobile‑First**

* **Layout base**: header compacto, *tab bar* inferior (Agenda, Vendas, Clientes, Mais) em telas <576px; em >=768px usar sidebar.
* **Grade**: usar colunas `col-12` por padrão; promover para `col-md-6`/`col-lg-4` em telas maiores.
* **Tabelas**: transformar em listas/cartões no mobile (empilhar labels + valores). Rows clicáveis.
* **Formulários**: inputs grandes (48px), teclado adequado (`inputmode`, `type=tel/email/number`), máscaras leves.
* **Ações rápidas** (FAB): “Novo agendamento”, “Nova venda”, “Novo cliente”.
* **Uploads**: câmera nativa para antes/depois (accept="image/*" capture="environment").
* **PWA**: manifest + service worker para ícone e *install prompt* (opcional no MVP).
* **Acessibilidade**: contraste, foco visível, `aria-*`, navegação por teclado.

---

## 7) Finanças & Comissões

* **Despesas fixas**: lançar aluguel/luz (ex.: R$300) e ratear por mês.
* **COGS**: consumo de insumo por sessão (deriva dos *kits*).
* **Comissão**: regra por profissional (fixo, %, mista). Job calcula ao fechar venda.
* **Relatórios**: DRE (Receita – COGS – Despesas – Comissões = Lucro), por mês e por serviço.

---

## 8) Segurança / LGPD

* Consentimento (propósitos: atendimento, marketing), revogação, trilha de auditoria de consentimentos.
* Termos/anuências em PDF anexo ao cliente/anamnese.
* Perfis: Dono(a), Profissional, Recepção (RBAC via Policies/Permissions).

---

## 9) Testes & Qualidade

* **Unit** (Services, cálculos de comissão/COGS), **Feature** (fluxos API), **Dusk** (UX crítica).
* PHPCS (PSR‑12), PHPStan lvl 6+, Parallel Lint, Composer Audit.
* Pipelines: lint → testes → artefatos → deploy (UAT → PRD).

---

## 10) Seeds iniciais (exemplo)

* Perfis/usuários de teste, 3 serviços (Limpeza de pele, Drenagem linfática, Radiofrequência), 8 produtos/insumos, 1 kit por serviço, 10 clientes fictícios.

---

## 11) Roadmap (Sprints)

* **Sprint 1 (MVP‑Core)**: Agenda, Clientes, Serviços, Vendas/Payments, Estoque básico, Relatórios diários, Consentimento.
* **Sprint 2**: Leads/UTM/CRM, Lembretes WhatsApp, Pacotes, Fidelidade, Comissões, DRE simples.
* **Sprint 3**: Antes/depois + medidas, NPS, Reativação 60/90d, Campanhas segmentadas, PWA.

---

## 12) Prompts para Agentes (Codex)

> Use verbos no imperativo; cada agente deve respeitar entradas/saídas e critérios de aceite.

### 12.1 Architect Agent

**Tarefa:** Gerar skeleton Laravel + Vue + AdminLTE, estrutura de diretórios por domínio, Providers e Policies.
**Entrada:** este `AGENTS.md`.
**Saída:** projeto Laravel com Vite e Vue 3; rotas básicas; guards; Service/Repository boilerplate; Policies de exemplo.
**Aceite:** build roda; rotas **/api/health** e **/web/health** OK; coding style PSR‑12.

### 12.2 Backend Agent (Laravel)

**Tarefa:** Implementar migrations, models, factories, seeders e Services/Repositories para domínios `Clients`, `Services`, `Appointments`, `Sales`, `Inventory`.
**Entrada:** ERD.
**Saída:** migrations + seeders; endpoints REST descritos; testes Feature básicos.
**Aceite:** `php artisan migrate:fresh --seed` ok; 95% dos endpoints CRUD funcionais com validação e Policies.

### 12.3 Frontend Agent (Vue/AdminLTE, **mobile‑first**)

**Tarefa:** Criar páginas responsivas: Agenda (FullCalendar), Clientes (lista/cartões), Vendas (POS), Estoque, Relatórios.
**Entrada:** rotas API.
**Saída:** telas responsivas com transições leves; componentes reutilizáveis.
**Aceite:** Lighthouse mobile Performance ≥ 80, Accessibility ≥ 90 (dev build).

### 12.4 Inventory Agent

**Tarefa:** Baixa automática por sessão via `service_kits`/`kit_items`; alerta de validade e min_stock.
**Aceite:** registrar sessão → `session_items` + `stock_movements(out)`.

### 12.5 Marketing/CRM Agent

**Tarefa:** Leads + UTMs, pipeline, cupons, reativação 60/90d, link WhatsApp para lembretes.
**Aceite:** funil visível, export CSV, disparo de mensagem via template (sem API paga no MVP).

### 12.6 Finance/Commission Agent

**Tarefa:** Comissões (fixo/%/misto), despesas, DRE, fluxo de caixa.
**Aceite:** relatório mensal fechado; conciliação soma pagamentos vs. vendas.

### 12.7 QA Agent

**Tarefa:** Configurar PHPCS, PHPStan, Pest/PHPUnit, Parallel Lint, Composer Audit; Git hooks.
**Aceite:** pipeline verde; cobertura mínima 70% domínios críticos.

### 12.8 Compliance Agent

**Tarefa:** Telas de consentimento/termos, auditoria de leitura, RBAC.
**Aceite:** fluxos LGPD mapeados; export/remoção de dados por cliente.

---

## 13) Critérios Gerais de Aceite (DoD)

* Build e testes verdes; linters limpos.
* Responsividade verificada em 360×640 e ≥1280px.
* A11y básica: sem contrastes < 4.5:1; labels associados; navegação por teclado.
* Logs de erro centralizados; mensagens de falha amigáveis.

---

## 14) Snippets úteis

### 14.1 Queries KPIs (exemplos)

```sql
-- No-show por profissional
SELECT professional_id,
  SUM(CASE WHEN status='no_show' THEN 1 ELSE 0 END)/COUNT(*) AS no_show_rate
FROM appointments GROUP BY professional_id;

-- Consumo/custo de insumos por mês
SELECT DATE_FORMAT(a.scheduled_at,'%Y-%m') ym,
       SUM(si.quantity_used * p.cost_per_unit) cogs
FROM session_items si
JOIN products p ON p.id=si.product_id
JOIN appointments a ON a.id=si.appointment_id
GROUP BY ym;
```

### 14.2 Componente Vue — card responsivo (esqueleto)

```vue
<template>
  <div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-column gap-2">
      <strong class="fs-6">{{ title }}</strong>
      <slot />
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-primary btn-sm" @click="$emit('primary')">Ação</button>
        <button class="btn btn-outline-secondary btn-sm" @click="$emit('secondary')">Outra</button>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ title: String })
</script>
<style scoped>
@media (max-width: 576px){ .card-body{ padding: .875rem; } }
</style>
```

---

## 15) Variáveis de Ambiente (exemplo `.env`)

```
APP_NAME=EsteticaERP
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estetica
DB_USERNAME=estetica
DB_PASSWORD=secret
QUEUE_CONNECTION=database
FILES_DISK=public
```

---

## 16) Observações finais

* Comece pelo **MVP** (Agenda, Clientes, Vendas, Estoque, Relatórios simples, Consentimento).
* Garanta **mobile‑first** desde o primeiro componente.
* Mantenha **kits de serviço** atualizados para refletir custo real por sessão.
* Meça desde o dia 1: **no‑show, ticket, reativação, ocupação**.
