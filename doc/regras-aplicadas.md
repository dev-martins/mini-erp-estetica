AGENDAMENTO

 - [x] não deve permitir agendamento anterior a dataHora atual
 - [x] os indicadores estão zerados mesmo tendo 2 agendamentos pra hoje: Ocupação, No-show, Ticket medio
 - [x] ajustes modal aumentar width e colocar mais campos ao lado do outro, se exceder certa altura adicionar scroll
 - [x] deve ter a opção de informar se o cliente compareceu ou não, pode ser na tela de agendamentos ou outra que considere mais pertinente. 
 - [ ] Se passou 24h e não foi informado se o cliente compareceu na data o sistema deve emitir um alerta para owner e reception e professional (no caso do ultimo apenas o professional para o qual o cliente estava agendado)
 - [ ] Se quando agendado o produto é reservado no estoque, quando for informado que o cliente não compareceu a reserva deve ser desfeita
 - [x] em próximos agendamentos abaixo do nome do procedimento deve ter a quantidade daquele agendamento tipo: 2/4 sessões
 - [ ] deve haver a possibilidade do funcionário logado apenas owner e recepção de cancelar o agendamento ou remarcar visto que o cliente pode entrar em cantato avisando que não poderá ir.
 - [ ] o Cancelamento ou remarcação daquela sessão só pode ser feito com antecedência mínima de 24h (quando for o cliente tentando cancelar ou remarcar).
 - [ ] Intervalo de no mínimo 48h entre uma sessão e outra.
 - [x] Por padrão deve aparecer em agendamento o card de próximos atendimentos (isso já funciona), porém preciso ter um filtro na página de agendamentos por mês, semana e dia. Quando filtrar devem aparecer no card de proximos atendimentos no máximo cinco agendamentos do mês, semana ou dia de acordo com o filtro. E o título do card deve mudar para agendamentos de outubro, agendamentos do dia x/mes a y/mes (no filtro de semana) e agendamentos do dia x/mes (agendamentos do dia). Para que o operador possa dizer se a pessoa compareceu ou não.  Nos filtros deve ter a opção de limpar para voltar a aparecer próximos agendamentos.
 - [x] Remover do calendário na visão de dia e semana os horários 00 a 06

VENDAS

 - [x] Adicionar um filtro na tela de vendas onde ao abrir a tela seja por padrão as vendas recentes. Porém possa filtrar por vendas do ano, mês, semana e dia. 
 - [x] Os indicadores de hoje, tiket médio, comissão e em mix de receitas: serviços, produtos e pacotes devem variar de acordo com o filtro da página. Na tabela que exibe vednas recentes qunado tiver exibindo o padrão máximo de 8 registros, quando estiver filtrando deve ter paginação com máximo de 8 por pagina
 - [x] no botão detalhes quando clicar deve abrir um modal e exibir os detalhes mais importantes da venda

TELA DE CLIENTES VISÃO FUNCIONÁRIOS

 - [x] quando clica no botão detalhes deve exibir os principais detalhes do cliente em um modal
 - [x] botão novo cliente deve permitir adicionar novo cliente, quando clicado deve abrir um modal de cadastro
 - [x] o card clientes ativos por padrão exibe clientes ativos. Preciso de um filtro para status dos clientes e uma forma de desativar e reativar clientes nessa tela, também preciso de uma paginação, com no máximo 5 clientes por página
 - [ ] Integrar o botão enviar whatsapp com o whatsapp
 - [ ] no card resumo rápido, o contador de clientes ativos e inativos está funcionando, porém inativei um cliente e depois o reativei, o contador de reativações 30d contina zerado. 

TELA DE ESTOQUE VISÃO FUNCIONÁRIOS

 - [ ] Botão registrar entrada deve abrir um modal para registro
 - [ ] baixa manual deve abrir um modal para registro de baixa

VISÃO DO CLIENTE

 - [x] Já existe seeder de clientes, porém não encontrei senha na seeder. Clientes precisam ter acesso ao sistema.
 - [x] Precisa ter uma api de cadastro de cliente, atualização de dados cadastrais e delete lógico. Já existe endpoint de listagem de todos os clientes.
 - [x] Clientes terão um pré cadastro que pode ser feito por um profissional: Owner, seretaria ou recepcionista, ou por meios externos como landpage etc. Será necessário informar nome, email ou celular (será enviado um email ou sms para validar email ou celular com código de primeiro acesso que ao informar será marcado na tabela que o meio de comunicação é válido) e uma senha.
 - [x] Só pode ver seus próprios agendamento.
 - [x] Só pode ver Suas próprias compras.
 - [ ] caso queira agendar deve ter um pacote assinado e deve ter pelo menos 1 sessão disponível no pacote que ele assinou respeitando a demais regras de agendamento já aplicadas;
 - [ ] Só pode remarcar ou cancelar com 24h de antecedência. 

VISÃO SECRETÁRIA/RECEPCIONISTA

 - [ ] pode visualizar e fazer marcação na agenda para todos os profissionais
 - [ ] pode cancelar agendamentos com até 24h de antecedencia
 - [ ] pode cadastrar clientes e ver detalhes dos clientes
 - [ ] pode visualizar estoque, registrar entrada e baixa manual, mas o sistema deve guardar sempre histórico se de quem registrou entrada ou deu baixa se foi manual ou pelo agendamento/compra de pacote
 - [ ] não deve ter visão de relatórios

VISÃO OWNER

 - [ ] tem visão de tudo, sem restrições
 - [ ] pode desmarcar um agendamento até minutos antes do da hora marcada
 - [ ] como tem acesso a todas as funcionalidades pode realizar agendamento para todos os profissionais
 - [ ] mesmo para o owner o sistema deve sempre auditar as ações

VISÃO DO PROFISSIONAL

 - [ ] pode visualizar apenas os clientes agendamentos para ele, ou seja, sua própria agenda
 - [ ] pode desmarcar com no mínimo 24h de antecedencia e agendar clientes para ele mesmo
 - [ ] não tem visão de vendas, clientes, estoque e relatórios. Apenas agenda

GASTOS
1 - plano de hospedagem / Hostinger + domínio = mensal: 29,00
2 - cadastro da marca no INPI / https://busca.inpi.gov.br/pePI/ = 150

## Política de Cancelamento e Reagendamento

  - [ ] O pacote deve ser usado no período de 90 dias a contar a partir do dia contratado;
  - [ ] Atendimento somente com hora marcada;
  - [ ] Reagendar ou desmarcar com antecendência mínima de 24h;
  - [ ] O não comparaceimento sem aviso prévio, contará como atendimento feito, sem direito a reagendamento daquela sessão;
  - [ ] O pacote é intrasferível (não podendo colocar outra pessoa no lugar).


Observaçoes

tela de vendas
O botão “Checkout rápido” está ali como atalho para abrir o fluxo de finalização de venda/POS imediatamente — a ideia é que, a partir de qualquer lugar da página de vendas, o atendente possa iniciar uma nova cobrança sem ter que navegar por menus. Ele ainda não dispara nenhuma ação (porque o fluxo completo de checkout não foi implementado), mas fica reservado para conectar com o modal ou a rota de criação de vendas assim que o POS estiver pronto.

Tela de clientes visão de funcionários

As “Listas inteligentes” que aparecem hoje na tela de clientes são apenas cartões estáticos simulando possíveis segmentações úteis (ex.: clientes sem retorno há 30 dias, pacotes prestes a vencer). Eles ainda não são gerados dinamicamente nem estão ligados a filtros automáticos—servem só como placeholder para futuras automações de CRM. Para transformá-las em listas reais, precisaríamos criar critérios (queries ou endpoints) que retornem aqueles subconjuntos e ligar cada botão a um filtro ou rota apropriada.
