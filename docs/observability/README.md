# Observabilidade, Logs Estruturados e Correlation ID

A observabilidade corporativa do **Comanda** foi estruturada sob os três pilares clássicos: logs estruturados, métricas de sistema/negócio e correlação de tráfego.

## 📝 1. Logs Estruturados JSON (LGPD Compliance)

Todas as classes de logging herdam de `BaseJsonLogService` e salvam os eventos em arquivos de texto no formato JSON de linha única em `storage/logs/`.
Essa estrutura é otimizada para ingestão automática por ferramentas como **Logstash**, **Splunk**, **Fluentd** ou **Elasticsearch**.

### Serviços de Logs Disponíveis
*   `ApplicationLogService`: Logs gerais de infraestrutura e aplicação (`application.json.log`).
*   `SecurityLogService`: Logs de autenticação, acessos indevidos e incidentes de segurança (`security.json.log`).
*   `BusinessLogService`: Eventos de negócios como abertura de comandas, vendas e pedidos (`business.json.log`).
*   `AuditLogService`: Auditorias de dados pessoais e privacidade de acordo com a LGPD (`audit.json.log`).

### Payload de Log Padrão
```json
{
  "timestamp": "2026-06-02T12:00:00-03:00",
  "level": "INFO",
  "action": "order.completed",
  "message": "Pedido finalizado pelo cliente.",
  "correlation_id": "8488e04b-bb12-4c28-9844-3866c1b3f9ff",
  "request_id": "019e85e5-f0cd-71ac-993d-d3ca10ab025c",
  "tenant": 1,
  "unit": 1,
  "user": 5,
  "ip": "192.168.1.15",
  "user_agent": "Mozilla/5.0...",
  "context": {
    "order_id": 99,
    "total_cents": 15000,
    "card_number": "[FILTERED]"
  }
}
```

> [!IMPORTANT]
> **Privacidade por Padrão (LGPD)**: O `BaseJsonLogService` possui um higienizador de contexto automático (`sanitizeContext`). Chaves confidenciais como `password`, `card_number`, `cvv`, `token`, `secret`, `signature` e `private_key` são automaticamente higienizadas para `[FILTERED]` antes da gravação física.

---

## 🔗 2. Correlation ID Global

Para suportar rastreamento distribuído em arquitetura de microsserviços ou sistemas distribuídos, o middleware `CorrelationIdMiddleware` gera um identificador único universal (UUID v4) para cada ciclo de vida de requisição HTTP.

*   **Propagação**: O Correlation ID é anexado como o cabeçalho HTTP `X-Correlation-ID` na resposta.
*   **Encapsulamento**: Caso a requisição de entrada já contenha o cabeçalho `X-Correlation-ID` (ex: vindo de um Gateway de API), ele é preservado e propagado adiante.
*   **Aplicações Práticas**: O Correlation ID é injetado automaticamente nos logs JSON, logs de auditoria, SSE e filas Redis, permitindo correlacionar todo o caminho de um erro específico.

---

## 📊 3. Métricas de Aplicação e Negócio

O `MetricsService` expõe um agregador de métricas unificado para consumo no dashboard administrativo ou via API em `/admin/metrics`.

### Métricas de Negócio (`BusinessMetricsService`)
*   `orders_last_hour`: Contagem de pedidos na última hora.
*   `average_ticket_cents`: Valor do ticket médio em centavos de todas as vendas ativas.
*   `sales_today_cents`: Faturamento consolidado do dia corrente.
*   `occupied_tables`: Quantidade de mesas físicas ocupadas.
*   `deliveries_in_progress`: Entregas de delivery com status atribuído ou despachado.
*   `orders_in_production`: Fila de preparo ativa na cozinha.
