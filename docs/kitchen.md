# DOCUMENTAÇÃO — COZINHA (PRODUCTION PIPELINE)

Os **Tickets de Cozinha** (`kitchen_tickets`) representam a fila operacional de produção física de alimentos e bebidas no estabelecimento.

---

## 1. Estrutura de Banco de Dados (`kitchen_tickets`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index)
* **`order_id`** (`bigint unsigned`, FK): Pedido de origem da produção.
* **`status`** (`varchar(30)`, default `'pending'`): pending, preparing, ready, completed, cancelled.
* **`sent_at`** (`datetime`): Horário do envio à cozinha.
* **`started_at`** (`datetime`, Nullable): Horário de início do preparo.
* **`ready_at`** (`datetime`, Nullable): Horário de término do preparo.
* **`completed_at`** (`datetime`, Nullable): Horário de retirada/conclusão da entrega.
* **`timestamps`** e **`softDeletes`**

---

## 2. Fila Reativa e Eventos (SSE)

O Painel de Cozinha opera estritamente sob eventos reativos em tempo real enviados através do canal `admin.kitchen`.
* `kitchen.created` (novo ticket pendente)
* `kitchen.preparing` (iniciado preparo)
* `kitchen.ready` (pedido pronto, dispara alerta no salão)
* `kitchen.completed` (pedido retirado)

---

## 3. Segurança e Políticas (`KitchenTicketPolicy`)

* **Tenant Isolation:** Apenas tickets pertencentes à empresa do funcionário da cozinha logado são expostos.
* **Privilégios:** Operações na produção exigem a permissão RBAC correspondente no sistema de papéis.
