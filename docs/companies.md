# DOCUMENTAÇÃO — EMPRESAS (COMPANIES)

O módulo de **Empresas** atua como o Tenant (locatário) centralizador de todo o ecossistema do **Comanda**. Todas as demais entidades (unidades físicas, usuários, funcionários, clientes, produtos e categorias) são isoladas sob o escopo de uma empresa proprietária.

---

## 1. Estrutura de Banco de Dados (`companies`)

A tabela `companies` armazena as informações corporativas e de licenciamento do tenant:

* **`id`** (`bigint unsigned`, Primary Key): ID interno sequencial de banco (nunca exposto externamente).
* **`uuid`** (`uuid`, Unique, Index): Identificador público de tráfego externo e URLs.
* **`status`** (`varchar(30)`, default `'active'`): Enum de governança (`active`, `suspended`).
* **`legal_name`** (`varchar(255)`): Razão Social corporativa.
* **`trade_name`** (`varchar(255)`): Nome Fantasia comercial.
* **`document_type`** (`varchar(30)`): Tipo de documento corporativo (`CNPJ`, `CPF`).
* **`document_number`** (`varchar(30)`, Unique, Index): Número do CNPJ/CPF da empresa.
* **`email`** (`varchar(150)`, Unique, Index): E-mail de contato do tenant.
* **`phone`** (`varchar(30)`): Telefone corporativo.
* **`timezone`** (`varchar(50)`, default `'America/Sao_Paulo'`): Fuso horário do tenant.
* **`currency`** (`varchar(10)`, default `'BRL'`): Moeda padrão de operação.
* **`language`** (`varchar(10)`, default `'pt_BR'`): Idioma do sistema.
* **`logo`** (`varchar(255)`, Nullable): Caminho físico ou URL do logotipo da empresa.
* **`settings_json`** (`json`, Nullable): Definições adicionais em formato JSON.
* **`timestamps`** (`created_at`, `updated_at`)
* **`softDeletes`** (`deleted_at`)

---

## 2. Fluxo de Escrita e Camadas

O cadastro de empresas segue rigorosamente a estrutura de camadas atômica:

1. **Request:** `CreateCompanyRequest` e `UpdateCompanyRequest`
   * Validações de CNPJ/CPF exclusivos, formatos e e-mails duplicados.
2. **DTO:** `CreateCompanyDTO` e `UpdateCompanyDTO`
   * Transporte imutável de dados tipados.
3. **Action:** `CreateCompanyAction` e `UpdateCompanyAction`
   * Orquestram transações SQL, geram o UUID público no boot e executam logs imutáveis através do `AuditService`.
4. **Model:** `Company`
   * Contém relacionamentos (hasMany `CompanyUnit`, `Product`, `Category`).

---

## 3. Segurança e Políticas (`CompanyPolicy`)

As permissões sobre o recurso de Empresas são restritas:

* **`viewAny`:** Permitido apenas a Usuários Administrativos globais.
* **`view` / `update`:** Funcionários vinculados à empresa podem visualizar ou editar seus próprios dados se possuírem a permissão `companies.view` / `companies.update`.
* **`create` / `delete`:** Exclusivo para usuários de governança master ou super-administradores do painel global.
