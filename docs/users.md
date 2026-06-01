# DOCUMENTAÇÃO — USUÁRIOS ADMINISTRATIVOS (USERS)

Os **Usuários Administrativos** representam os operadores gerais de painéis que controlam as diretrizes corporativas da empresa, faturamento, licenciamento e criação de filiais.

---

## 1. Estrutura de Banco de Dados (`users`)

Operando sob a tabela nativa estendida do Laravel, com novos campos para governança corporativa:

* **`id`** (`bigint unsigned`, PK)
* **`uuid`** (`uuid`, Unique, Index): Identificador público.
* **`name`** (`varchar(255)`): Nome completo do usuário.
* **`email`** (`varchar(150)`, Unique, Index): E-mail de acesso.
* **`password`** (`varchar(255)`): Hash de senha seguro.
* **`status`** (`varchar(30)`, default `'active'`): active, inactive, suspended.
* **`timestamps`** e **`softDeletes`**

---

## 2. Regras Rígidas de Governança e Segurança

1. **Separação de Identidade e Guards:**
   * A tabela `users` é exclusiva para operadores de backoffice geral e administradores do ecossistema.
   * **NUNCA** utilize registros da tabela `users` para funcionários de loja (garçons, caixas) ou para clientes finais.
2. **Autoexclusão Impedida:**
   * O sistema proíbe, por meio da `UserPolicy`, que o operador logado exclua o próprio registro administrativo, prevenindo bloqueios acidentais e perda de rastreabilidade de auditoria.
3. **Logs de Auditoria:**
   * Qualquer alteração de dados administrativos (nome, status ou reset de senha) gera um registro de auditoria imutável detalhando o estado anterior e posterior da operação.
