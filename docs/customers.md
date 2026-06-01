# DOCUMENTAÇÃO — CLIENTES (CUSTOMERS)

O módulo de **Clientes** (`customers`) armazena os registros de consumidores do ecossistema do **Comanda**, abrangendo atendimentos presenciais, delivery, programas de fidelidade e CRM corporativo.

---

## 1. Estrutura de Banco de Dados (`customers`)

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index): Identificador público.
* **`company_id`** (`bigint unsigned`, FK): Empresa de cadastro original do cliente.
* **`name`** (`varchar(150)`): Nome completo.
* **`email`** (`varchar(150)`, Unique, Index): E-mail do cliente.
* **`password`** (`varchar(255)`): Senha criptografada (opcional para canais de autoatendimento simples).
* **`phone`** (`varchar(30)`, Nullable): Telefone higienizado.
* **`document`** (`varchar(20)`, Nullable, Unique): CPF do cliente para fins fiscais.
* **`birth_date`** (`date`, Nullable): Data de nascimento para campanhas de aniversariantes.
* **`marketing_opt_in`** (`boolean`, default `false`): Consentimento para receber campanhas de marketing/CRM (em conformidade com a LGPD).
* **`status`** (`varchar(30)`, default `'active'`): active, suspended.
* **`timestamps`** e **`softDeletes`**

---

## 2. Conformidade com a LGPD (Marketing Opt-In e Proteção de Credenciais)

Em estreito alinhamento com a Lei Geral de Proteção de Dados (LGPD):
* O consentimento de marketing (`marketing_opt_in`) é explícito e armazenado como booleano no banco de dados.
* O tratamento dos dados do cliente (CPF, e-mail e telefone) é restrito à finalidade operacional de faturamento e entrega de pedidos (base legal de execução de contrato).
* **Criação de Clientes no Checkout de Delivery:** Clientes criados automaticamente pelo checkout de delivery **não possuem senhas padrão previsíveis**. Para fins de segurança máxima e minimização da superfície de ataque, eles nascem com uma senha gerada aleatoriamente de alta entropia (inutilizável, contendo 40 caracteres randômicos criptográficos), impedindo qualquer tipo de acesso não autorizado baseado em credenciais fixas ou de teste. O acesso subsequente é projetado para operar sob fluxos de OTP (One-Time Password) ou Magic Links.

---

## 3. Segurança e Políticas (`CustomerPolicy`)

* O isolamento relacional garante que o cadastro de clientes pertença de forma exclusiva ao tenant cadastrador (`company_id`).
* Operadores de loja (funcionários) podem gerenciar o cadastro de clientes se possuírem a permissão `customers.view` / `customers.create` / `customers.update`.
* A senha do cliente (`password`) é marcada como oculta (`$hidden` na model `Customer`) e jamais é exposta em logs, auditorias, respostas de API JSON ou eventos SSE.

