# Monitoramento e Diagnósticos de Saúde (Health Checks)

O sistema **Comanda** possui um subsistema de monitoramento integrado para garantir visibilidade da saúde da infraestrutura e aplicação em produção.

## 🏥 Endpoints de Saúde (Health Checks)

Todas as verificações de integridade de serviços retornam respostas no formato estruturado JSON e utilizam códigos de status HTTP corretos (ex: `200 OK` para saudável, `503 Service Unavailable` se algum canal essencial estiver indisponível).

### 1. Liveness Probe (Integridade Básica)
Indica se o container PHP-FPM está online e capaz de responder a requisições HTTP básicas.
*   **Rota**: `/liveness` ou `/api/health/live`
*   **Método**: `GET`
*   **Acesso**: Público
*   **Exemplo de Resposta (200 OK)**:
    ```json
    {
      "success": true,
      "status": "alive",
      "timestamp": "2026-06-02T12:00:00-03:00"
    }
    ```

### 2. Readiness Probe (Prontidão para Tráfego)
Verifica se os principais subsistemas de tráfego (Banco de Dados, Redis e Cache) estão prontos para processar tráfego real de requisições.
*   **Rota**: `/readiness` ou `/api/health/ready`
*   **Método**: `GET`
*   **Acesso**: Público
*   **Exemplo de Resposta (200 OK)**:
    ```json
    {
      "success": true,
      "status": "ready",
      "timestamp": "2026-06-02T12:00:00-03:00",
      "services": {
        "database": {
          "status": "up",
          "details": "Database connection successful."
        },
        "redis": {
          "status": "up",
          "details": "Redis connection successful."
        },
        "cache": {
          "status": "up",
          "details": "Cache read/write test successful."
        }
      }
    }
    ```

### 3. Full Diagnostics Probe (Diagnóstico Completo)
Realiza a varredura completa em todos os subsistemas integrados da aplicação (incluindo licença, SSE, impressão, armazenamento e manifestos PWA).
*   **Rota**: `/health` ou `/api/health/full`
*   **Método**: `GET`
*   **Acesso**: Restrito ou público (conforme política de hardening)
*   **Exemplo de Resposta (200 OK)**:
    ```json
    {
      "success": true,
      "status": "healthy",
      "timestamp": "2026-06-02T12:00:00-03:00",
      "services": {
        "database": {"status": "up", "details": "Database connection successful."},
        "redis": {"status": "up", "details": "Redis connection successful."},
        "queue": {"status": "up", "details": "Queue is operational."},
        "storage": {"status": "up", "details": "Storage path is writable."},
        "license": {"status": "up", "details": "License is active."},
        "sse": {"status": "up", "details": "SSE channel is operational via Redis pub/sub."},
        "printing": {"status": "up", "details": "Print table exists. Pending print jobs: 0"},
        "cache": {"status": "up", "details": "Cache read/write test successful."},
        "pwa": {"status": "up", "details": "PWA assets (manifest.json, sw.js) are present."}
      }
    }
    ```

---

## 📈 Integrações Recomendadas para Alertas
*   **Prometheus / Grafana**: Realizar scraping no endpoint `/api/health/full` a cada 15-30 segundos.
*   **Alertmanager**: Configurar alertas se o status HTTP retornar `503` ou se o valor `success` for `false` por mais de 3 tentativas consecutivas.
