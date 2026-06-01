# DOCUMENTAÇÃO — CATEGORIAS (CATEGORIES)

As **Categorias** (`categories`) estruturam o cardápio e catálogo de vendas, facilitando a navegação nos canais de venda (PDV, Cardápio Digital, Totens, Mesas e Delivery).

---

## 1. Estrutura de Banco de Dados (`categories`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index): Identificador público de tráfego.
* **`company_id`** (`bigint unsigned`, FK): Empresa proprietária da categoria.
* **`name`** (`varchar(150)`): Nome da categoria (ex: Massas, Sobremesas, Cervejas).
* **`description`** (`text`, Nullable): Breve descrição da categoria de produtos.
* **`status`** (`varchar(30)`, default `'active'`): active, inactive.
* **`sort_order`** (`integer`, default `0`): Índice numérico de ordenação para as listagens de cardápio.
* **`timestamps`** e **`softDeletes`**

---

## 2. Ordenação Personalizada

O campo `sort_order` permite que o gestor decida a sequência exata de exibição das categorias no PDV ou no cardápio digital (ex: Entradas ordenadas com sort_order = 1, Pratos Principais = 2, Sobremesas = 3), permitindo flexibilidade total de layout.

---

## 3. Segurança e Políticas (`CategoryPolicy`)

* **Isolamento de Tenant:** Apenas categorias pertencentes à própria empresa do operador são expostas nas queries e APIs.
* **Privilégios:** A criação, edição e exclusão de categorias exige permissão correspondente no RBAC (`categories.create`, `categories.update`, `categories.delete`).
