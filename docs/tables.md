# DOCUMENTAÇÃO — MESAS (TABLES)

As **Mesas** (`tables`) representam os pontos físicos de consumo no estabelecimento.

---

## 1. Estrutura de Banco de Dados (`tables`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index): Identificador público.
* **`company_id`** (`bigint unsigned`, FK): Isolamento multi-tenant da empresa.
* **`unit_id`** (`bigint unsigned`, FK): Unidade/filial correspondente.
* **`code`** (`varchar(50)`): Identificador curto da mesa (ex: M01, VIP2).
* **`name`** (`varchar(100)`): Rótulo amigável (ex: Mesa 01).
* **`capacity`** (`integer`): Capacidade máxima de pessoas sentadas.
* **`sector`** (`varchar(100)`): Setor/salão físico (ex: Varanda, Principal).
* **`status`** (`varchar(30)`, default `'available'`): available, occupied, reserved, blocked, cleaning.
* **`sort_order`** (`integer`, default `10`): Ordenação visual nos mapas.
* **`timestamps`** e **`softDeletes`**

---

## 2. Realtime e Reatividade (SSE)

Qualquer alteração no status de uma mesa (ex: livre para ocupada no lançamento de comanda, ou ocupada para limpeza no encerramento) emite instantaneamente eventos reativos para a UI no canal `admin.tables`.
Os mapas de mesas reagem instantaneamente sem refresh.

---

## 3. Segurança e Políticas (`TablePolicy`)

* **Tenant Isolation:** Queries filtradas estritamente por `company_id`.
* **Privilégios:** Permissões RBAC (`tables.view`, `tables.create`, `tables.update`, `tables.delete`) governam as operações do garçom e do admin.
