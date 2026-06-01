# DOCUMENTAÇÃO — CAIXA (CASHIER SHIFTS)

O **Caixa Operacional** (`cashier_shifts`) representa a base inicial para registrar os turnos diários de movimentação financeira no estabelecimento.

---

## 1. Estrutura de Banco de Dados (`cashier_shifts`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index)
* **`company_id`** (`bigint unsigned`, FK)
* **`unit_id`** (`bigint unsigned`, FK)
* **`opened_by`** (`bigint unsigned`, FK): Operador de caixa que abriu o turno.
* **`closed_by`** (`bigint unsigned`, FK, Nullable): Operador de caixa que fechou o turno.
* **`opened_at`** (`datetime`)
* **`closed_at`** (`datetime`, Nullable)
* **`opening_amount_cents`** (`bigint`): Valor de fundo de caixa inicial em centavos.
* **`closing_amount_cents`** (`bigint`, Nullable): Valor de fechamento contado em centavos.
* **`status`** (`varchar(30)`, default `'open'`): open, closed.
* **`timestamps`** e **`softDeletes`**

---

## 2. Conciliação e Análise Financeira

* **Quebra de Caixa:** Quando o valor de fechamento é inferior ao valor de abertura (diferença negativa), indicando a falta de dinheiro no caixa.
* **Sobra de Caixa:** Quando o valor de fechamento é superior ao de abertura (diferença positiva), indicando excesso.
* Toda operação de caixa é registrada de forma imutável nos logs de auditoria (`AuditService`).

---

## 3. Segurança e Políticas (`CashierShiftPolicy`)

* **Tenant Isolation:** Rigoroso isolamento por `company_id`.
* **Privilégios:** Operações de caixa exigem permissão correspondente no RBAC (`cashier.open`, `cashier.close`, `cashier.view`).
