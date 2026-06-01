# SEGURANÇA, RBAC E AUDITORIA — COMANDA

Este documento descreve os padrões de controle de acessos (RBAC), autorização via gates/policies e a arquitetura do motor de auditoria centralizado do **Comanda**.

## 1. Controle de Acessos Baseado em Funções (RBAC)
O sistema implementa um controle granular de permissões baseado na relação entre usuários, funções (Roles) e permissões (Permissions).

```text
Usuário (User/Employee) ──> UserRole ──> Role ──> RolePermission ──> Permission
```

* **Permissões Catalogaas:** As permissões são nomeadas seguindo o padrão de nomenclatura `modulo.recurso.acao` (ex: `orders.status.update`, `products.inventory.write`).
* **Abstração:** Toda verificação de permissão no backend deve passar obrigatoriamente por Policies ou Gates do Laravel. O frontend React ou Blade apenas consome essas permissões via API, nunca determinando regras por conta própria.

---

## 2. Separação Estrita de Identidades
O banco de dados armazena as seguintes entidades de forma totalmente separada, com guards de autenticação isolados:

1. **Administradores (`users`):** Usuários do painel de administração geral da empresa.
2. **Funcionários (`employees`):** Operadores (Caixas, Garçons, Cozinheiros, Gerentes de Unidade).
3. **Clientes (`customers`):** Clientes finais do site de delivery.

É terminantemente proibido misturar registros ou credenciais dessas tabelas.

---

## 3. Arquitetura do Sistema de Auditoria
Operações críticas e sensíveis de negócios obrigatoriamente disparam registros de auditoria via `AuditService`.

### Operações Auditadas Obrigatórias:
* Autenticação (Login, Logout, Tentativas falhas, Bloqueio de IP).
* Pagamentos e Fechamento de Caixa.
* Cancelamento e descontos em comandas e pedidos.
* Alteração, renovação ou tentativa de fraude em Licenças.
* Exportação de dados sensíveis ou relatórios financeiros.

### Dados Registrados em cada Registro:
```json
{
  "uuid": "439281a8-829b-43d9-a9a3-a5c7c2512111",
  "actor_id": 42,
  "actor_type": "App\\Models\\Employee",
  "action": "order.discount.apply",
  "ip_address": "192.168.1.15",
  "user_agent": "Mozilla/5.0...",
  "payload_before": {
    "discount_amount": 0
  },
  "payload_after": {
    "discount_amount": 1500
  },
  "context": {
    "reason": "Cortesia gerência",
    "order_uuid": "e3b381a8-829b-43d9-a9a3-a5c7c2512a81"
  },
  "created_at": "2026-06-01T14:35:00Z"
}
```
Os dados de auditoria são imutáveis e persistidos no banco de dados na tabela `audit_logs` e espelhados em logs rotacionados para segurança adicional e arquivamento legal.
