# DOCUMENTAÇÃO — PRODUTOS (PRODUCTS)

O módulo de **Produtos** (`products`) gerencia o catálogo de itens de venda do ecossistema do **Comanda**.

---

## 1. Estrutura de Banco de Dados (`products`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index)
* **`company_id`** (`bigint unsigned`, FK): Empresa proprietária do produto.
* **`category_id`** (`bigint unsigned`, FK): Categoria vinculada com restrição `onDelete(restrict)`.
* **`sku`** (`varchar(100)`, Nullable): SKU único por empresa.
* **`barcode`** (`varchar(100)`, Nullable, Index): Código de barras.
* **`name`** (`varchar(255)`): Nome de comercialização do produto.
* **`description`** (`text`, Nullable): Descrição detalhada ou lista de alérgenos/ingredientes.
* **`price_cents`** (`bigint`): Preço de venda armazenado como inteiro em centavos (ex: R$ 29,90 = `2990`).
* **`cost_cents`** (`bigint`, Nullable): Preço de custo armazenado em centavos para apuração de lucro (LTV).
* **`status`** (`varchar(30)`, default `'active'`): active, inactive.
* **`image`** (`varchar(255)`, Nullable): URL ou caminho físico da imagem do produto.
* **`preparation_time`** (`integer`, default `0`): Tempo estimado de produção na cozinha em minutos.
* **`timestamps`** e **`softDeletes`**

---

## 2. Regra Rígida de Integridade Financeira (Sem Float)

Para evitar erros acumulados de arredondamento de floats comuns em sistemas comerciais, todo valor monetário (`price_cents` e `cost_cents`) é armazenado como inteiro de alta precisão (`bigInteger`) representando centavos. O `ProductRequest` converte o valor decimal do input do usuário automaticamente para inteiro no prepareForValidation.

---

## 3. Segurança e Políticas (`ProductPolicy`)

* **Tenant Bound:** Produtos são fortemente acoplados à empresa do operador logado.
* **Validação Operacional:** O `preparation_time` em minutos alimentará o painel de monitoramento da cozinha nas fases futuras.
