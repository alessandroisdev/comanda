# DOCUMENTAÇÃO — COMANDAS (OPERATIONAL SESSIONS)

As **Comandas** (`orders_sessions`) representam as sessões operacionais de consumo dos clientes em uma mesa ou de forma individual (ficha).

---

## 1. Estrutura de Banco de Dados (`orders_sessions`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index)
* **`company_id`** (`bigint unsigned`, FK)
* **`unit_id`** (`bigint unsigned`, FK)
* **`table_id`** (`bigint unsigned`, FK, Nullable): Mesa associada (opcional para comandas avulsas).
* **`opened_by_employee_id`** (`bigint unsigned`, FK): Operador/Garçom que iniciou o atendimento.
* **`closed_by_employee_id`** (`bigint unsigned`, FK, Nullable): Operador que encerrou.
* **`status`** (`varchar(30)`, default `'open'`): open, closed, cancelled.
* **`opened_at`** (`datetime`): Timestamp de abertura.
* **`closed_at`** (`datetime`, Nullable): Timestamp de fechamento.
* **`people_count`** (`integer`, default `1`): Número de pessoas no atendimento.
* **`notes`** (`text`, Nullable)
* **`timestamps`** e **`softDeletes`**

---

## 2. Fluxo de Operações

* **Abrir Comanda:** Marca a mesa associada como `occupied`. Emite evento `session.opened`.
* **Encerrar Comanda:** Calcula o total, fecha a sessão e coloca a mesa correspondente em `cleaning` (limpeza). Emite `session.closed`.
* **Transferir Mesa:** Desvincula a mesa antiga, marca como `available` e ocupa a mesa destino. Emite `session.transferred`.
* **Mesclar Comandas:** Transfere todos os pedidos de uma comanda de origem para a de destino e cancela a de origem liberando a mesa antiga. Emite `session.merged`.

---

## 3. Segurança e Políticas (`OrderSessionPolicy`)

* **Tenant Isolation:** Estrito por `company_id` nas queries e Actions.
* **Privilégios:** Controlado pelas permissões RBAC (`sessions.open`, `sessions.close`, `sessions.view`).
