# DOCUMENTAÇÃO — UNIDADES FÍSICAS (UNITS)

O módulo de **Unidades Físicas** (`company_units`) fornece suporte a operações multi-unidades, franquias e filiais para o ecossistema do **Comanda**. Uma empresa pode possuir uma ou mais unidades físicas associadas.

---

## 1. Estrutura de Banco de Dados (`company_units`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index): Identificador público de tráfego externo.
* **`company_id`** (`bigint unsigned`, FK): Vinculado a `companies.id` com restrição `onDelete(restrict)`.
* **`status`** (`varchar(30)`, default `'active'`): active, inactive.
* **`name`** (`varchar(255)`): Nome de exibição da filial (ex: Filial Centro).
* **`document_number`** (`varchar(30)`, Nullable, Unique): CNPJ exclusivo da filial.
* **`email`** (`varchar(150)`, Nullable): E-mail da filial.
* **`phone`** (`varchar(30)`, Nullable): Telefone de contato da unidade.
* **`zipcode`** (`varchar(15)`): CEP do endereço.
* **`street`** (`varchar(255)`): Rua/Logradouro.
* **`number`** (`varchar(30)`): Número físico.
* **`district`** (`varchar(150)`): Bairro.
* **`city`** (`varchar(150)`): Cidade.
* **`state`** (`varchar(5)`): Estado (UF).
* **`country`** (`varchar(100)`, default `'Brasil'`)
* **`settings_json`** (`json`, Nullable): Configurações específicas da filial (ex: se aceita delivery próprio, horários de pico).
* **`timestamps`** e **`softDeletes`**

---

## 2. Governança e Isolamento Multitenant

As filiais mantêm isolamento absoluto. Um funcionário pertencente à Filial A (Empresa X) nunca obterá acesso a queries, comandas, mesas ou produtos vinculados à Filial B. 
A restrição relacional é controlada em nível de query pelo scope e validada nas políticas de acesso.

---

## 3. Segurança e Políticas (`CompanyUnitPolicy`)

* **`viewAny`:** Permitido se o operador possuir a permissão `units.view`. Se for funcionário de loja, o escopo filtra automaticamente apenas as filiais da sua própria empresa.
* **`view` / `update`:** Restringe apenas para funcionários da própria empresa vinculada à filial.
* **`create` / `delete`:** Exige permissões `units.create` / `units.delete` e restringe ao escopo do tenant correspondente.
