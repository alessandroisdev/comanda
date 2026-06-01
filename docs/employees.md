# DOCUMENTAÇÃO — FUNCIONÁRIOS (EMPLOYEES)

O módulo de **Funcionários** (`employees`) gerencia a equipe operacional de loja responsável pelo atendimento nas mesas, caixa, cozinha e entregas do ecossistema do **Comanda**.

---

## 1. Estrutura de Banco de Dados (`employees`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index): Identificador público de tráfego externo.
* **`company_id`** (`bigint unsigned`, FK): Vinculado a `companies.id` com restrição `onDelete(restrict)`.
* **`unit_id`** (`bigint unsigned`, FK, Nullable): Unidade/filial física onde o funcionário atua. Se nulo, representa um funcionário global/gerente de todas as filiais.
* **`employee_number`** (`varchar(50)`): Número de matrícula ou registro único do funcionário por empresa.
* **`name`** (`varchar(150)`): Nome completo do funcionário.
* **`email`** (`varchar(150)`, Unique, Index): E-mail de acesso corporativo.
* **`password`** (`varchar(255)`): Senha de acesso.
* **`phone`** (`varchar(30)`, Nullable): Telefone higienizado.
* **`document`** (`varchar(20)`, Nullable, Unique): CPF do funcionário.
* **`birth_date`** (`date`, Nullable): Data de nascimento.
* **`hire_date`** (`date`, Nullable): Data de contratação física.
* **`role`** (`varchar(50)`, default `'waiter'`): Cargo operacional mapeado pelo Enum `EmployeeRoleEnum` (`waiter`, `cashier`, `kitchen`, `manager`, `admin`, `driver`).
* **`status`** (`varchar(30)`, default `'active'`): active, suspended.
* **`timestamps`** e **`softDeletes`**

---

## 2. Higienização Automática de Dados (Form Requests)

A validação de funcionários no `CreateEmployeeRequest` e `UpdateEmployeeRequest` executa a higienização de strings no hook `prepareForValidation`:
* CPF e Telefone são limpos via regex (`preg_replace('/[^0-9]/', '', $input)`), armazenando estritamente caracteres numéricos limpos no banco, aumentando a consistência de busca e indexação.

---

## 3. Segurança e Políticas (`EmployeePolicy`)

* **`viewAny`:** Restringe as listagens aos funcionários da própria empresa.
* **`update` / `delete`:** Impede que um funcionário exclua o próprio registro operacional, forçando a deleção por terceiros ou gerentes de governança.
