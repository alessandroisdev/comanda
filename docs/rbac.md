# DOCUMENTAÇÃO — CONTROLE DE ACESSO BASEADO EM PAPÉIS (RBAC)

O ecossistema do **Comanda** implementa um sistema rígido de controle de acesso baseado em papéis (RBAC - Role-Based Access Control) que governa as ações operacionais e administrativas dos funcionários de loja e usuários de painéis.

---

## 1. Estrutura de Tabelas Associativas

* **`roles`:** Nomes dos perfis do sistema (ex: `super_admin`, `manager`, `cashier`, `waiter`, `kitchen`).
* **`permissions`:** Ações atômicas permitidas (ex: `companies.view`, `products.create`).
* **`role_permission`:** Pivot associando permissões às funções.
* **`employee_role`:** Pivot associando os papéis aos funcionários.

---

## 2. Catálogo de Permissões da Fase 2

O catálogo físico de permissões cadastrado pelo `RbacSeeder` é o seguinte:

```text
- companies.view    - Exibir dados da Empresa
- companies.create  - Cadastrar nova Empresa
- companies.update  - Editar dados da Empresa
- companies.delete  - Excluir Empresa (Soft Delete)

- units.view        - Visualizar Unidades Físicas
- units.create      - Cadastrar Unidades Físicas
- units.update      - Editar Unidades Físicas
- units.delete      - Excluir Unidades Físicas

- users.view        - Visualizar Usuários Administrativos
- users.create      - Cadastrar Usuários Administrativos
- users.update      - Editar Usuários Administrativos
- users.delete      - Excluir Usuários Administrativos

- employees.view    - Visualizar Funcionários
- employees.create  - Cadastrar Funcionários
- employees.update  - Editar Funcionários
- employees.delete  - Excluir Funcionários

- customers.view    - Visualizar Clientes
- customers.create  - Cadastrar Clientes
- customers.update  - Editar Clientes
- customers.delete  - Excluir Clientes

- categories.view   - Visualizar Categorias
- categories.create - Cadastrar Categorias
- categories.update - Editar Categorias
- categories.delete - Excluir Categorias

- products.view     - Visualizar Produtos
- products.create   - Cadastrar Produtos
- products.update   - Editar Produtos
- products.delete   - Excluir Produtos

- modules.view      - Visualizar Módulos e Licenciamento
```

---

## 3. Matriz de Perfis e Permissões Base

1. **`super_admin`:** Possui acesso completo a todas as permissões do sistema.
2. **`manager`:** Gerencia filiais, funcionários, clientes, produtos e categorias da sua própria empresa.
3. **`cashier`:** Visualiza produtos, categorias e clientes (para faturamento rápido do PDV).
4. **`waiter`:** Visualiza produtos e categorias (para emissão de pedidos nas mesas).
5. **`kitchen`:** Visualiza produtos (para monitoramento dos pedidos na cozinha).
