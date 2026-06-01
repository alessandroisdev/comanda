# DOCUMENTAÇÃO — PEDIDOS & ITENS (ORDERS & ITEMS)

Os **Pedidos** (`orders`) e **Itens do Pedido** (`order_items`) representam os lançamentos financeiros e de insumos vinculados a uma comanda operante.

---

## 1. Estrutura de Banco de Dados (`orders`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index)
* **`company_id`** (`bigint unsigned`, FK)
* **`unit_id`** (`bigint unsigned`, FK)
* **`session_id`** (`bigint unsigned`, FK): Comanda à qual o pedido está anexado.
* **`employee_id`** (`bigint unsigned`, FK): Garçom/Operador que registrou o pedido.
* **`order_number`** (`varchar(50)`): Código amigável e único por unidade (ex: PED-012345).
* **`status`** (`varchar(30)`, default `'draft'`): draft, pending, sent_to_kitchen, preparing, ready, delivered, cancelled.
* **`subtotal_cents`** (`bigint`): Total de itens em centavos (evita float!).
* **`discount_cents`** (`bigint`, default `0`): Desconto em centavos.
* **`total_cents`** (`bigint`): Total geral líquido em centavos.
* **`notes`** (`text`, Nullable)
* **`timestamps`** e **`softDeletes`**

---

## 2. Estrutura de Banco de Dados (`order_items`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index)
* **`order_id`** (`bigint unsigned`, FK)
* **`product_id`** (`bigint unsigned`, FK)
* **`quantity`** (`integer`): Quantidade do item.
* **`unit_price_cents`** (`bigint`): Preço unitário do produto no momento do lançamento.
* **`total_price_cents`** (`bigint`): Multiplicação da quantidade pelo preço unitário em centavos.
* **`notes`** (`varchar(250)`, Nullable): Observações (ex: "Sem cebola", "Ponto menos").

---

## 3. Transacionalidade e Recálculo

Nenhuma alteração de itens (adição, remoção, alteração de quantidade) ocorre fora de transação de banco de dados (`DB::transaction()`).
Cada alteração dispara a Action `RecalculateOrderTotalsAction` que recalcula o `subtotal_cents` e `total_cents` somando estritamente em inteiros (centavos), evitando erros de arredondamento de float em auditorias financeiras.
