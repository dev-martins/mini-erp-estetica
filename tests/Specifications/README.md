# Especificações de Regras de Negócio

Este índice vincula cada regra catalogada em `doc/regras-aplicadas.md` aos testes automatizados que a cobrem. Sempre que uma nova regra for adicionada ou alterada, atualize esta tabela e garanta que o(s) teste(s) correspondente(s) sejam criados/ajustados.

| Regra (`doc/regras-aplicadas.md`) | Classe de Teste | Tipo |
| --- | --- | --- |
| _Adicionar referência aqui_ | `_pending_` | `_pending_` |

## Como usar
- **1.** Atualize `doc/regras-aplicadas.md` com a nova regra.
- **2.** Crie/ajuste o teste apropriado (unit/service/feature/e2e) nos diretórios descritos em `doc/testing-strategy.md`.
- **3.** Substitua `_pending_` nesta tabela pelo caminho real da classe de teste (ex.: `tests/Feature/Api/Appointments/AppointmentCancellationTest.php`) e o tipo (`Unit`, `Service`, `Feature`, `E2E`).
- **4.** Se múltiplos testes cobrirem a mesma regra, adicione linhas adicionais.
- **5.** Execute `composer verify-rules-map` para garantir que nenhuma regra ficou sem cobertura catalogada.

> Dica: para regras com múltiplos cenários (ex.: cancelamento por role), utilize Pest `datasets` e aponte para a mesma classe de teste.
