# DOCUMENTAÇÃO — IMPRESSÃO TÉRMICA (PRINT JOBS)

A **Fila de Impressão** (`print_jobs`) centraliza os trabalhos de impressão térmica de tickets e extratos operacionais do Comanda.

---

## 1. Estrutura de Banco de Dados (`print_jobs`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index)
* **`company_id`** (`bigint unsigned`, FK)
* **`unit_id`** (`bigint unsigned`, FK)
* **`type`** (`varchar(50)`): Tipo de ticket (ex: kitchen_ticket, order_invoice).
* **`payload`** (`json`): Dados estruturados para montagem física do layout (itens, quantidades, cabeçalho).
* **`status`** (`varchar(30)`, default `'pending'`): pending, processing, printed, failed.
* **`attempts`** (`integer`, default `0`): Tentativas de processamento do job.
* **`created_at`** e **`updated_at`**

---

## 2. Isolamento de Hardware

A aplicação web principal **não** acessa hardware ou impressoras locais diretamente.
Toda impressão gera um registro em `print_jobs`.
Uma bridge local (ex: app desktop rodando na rede do cliente) consome a API através de pooling de jobs pendentes (`status = 'pending'`), renderiza localmente na impressora ESC/POS do caixa ou da cozinha, e atualiza o status do job para `printed` ou `failed` de forma assíncrona.

---

## 3. Logs de Auditoria

Qualquer falha de impressão é registrada de forma granular para fins de suporte técnico e auditoria operacional do estabelecimento.
