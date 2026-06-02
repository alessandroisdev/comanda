# Plano de Disaster Recovery (Plano de Recuperação de Desastres)

Este documento estabelece as diretrizes de recuperação de desastres (Disaster Recovery), RTO e RPO para o ecossistema **Comanda**.

## 📊 Métricas de DR (RTO e RPO)

*   **RPO (Recovery Point Objective - Objetivo de Ponto de Recuperação)**: **24 horas**. Indica que, no pior cenário, o estabelecimento poderá perder no máximo 24 horas de lançamentos/vendas, garantido pela execução diária de backups automatizados.
*   **RTO (Recovery Time Objective - Objetivo de Tempo de Recuperação)**: **2 horas**. Indica o tempo limite para restabelecer a operação total do sistema em um novo nó físico/servidor após um incidente grave.

---

## 🛡️ Matriz de Resolução de Incidentes

### 1. Perda ou Falha de Servidor Físico
*   **Ação**:
    1.  Prover nova instância do container usando Docker Compose:
        ```bash
        docker compose up -d --build
        ```
    2.  Restaurar o backup mais recente gerado no storage persistente ou S3:
        ```bash
        php artisan comanda:backup:restore {backup_id}
        ```
*   **Responsável**: Engenheiro de DevOps / Sysadmin.

### 2. Corrupção ou Perda de Banco de Dados MySQL
*   **Ação**:
    1.  Interromper conexões na aplicação ativando modo de manutenção temporário:
        ```bash
        php artisan down --secret="bypass-key"
        ```
    2.  Verificar o checksum e restaurar a base de dados SQL a partir de um backup criptografado válido:
        ```bash
        php artisan comanda:backup:restore {backup_id}
        ```
    3.  Voltar aplicação online:
        ```bash
        php artisan up
        ```

### 3. Perda de Conexão do Redis (SSE e Fila)
*   **Ação**:
    1.  Caso o Redis caia, o `LicenseValidator` e o cache local utilizarão de forma resiliente o driver de arquivos (File Cache) para validação local em grace period de até 7 dias.
    2.  Monitoramento dispara alerta. DevOps realiza reinício do serviço Redis no Docker:
        ```bash
        docker compose restart redis
        ```

### 4. Perda de Conexão com Impressoras Térmicas
*   **Ação**:
    1.  Os trabalhos de impressão não são perdidos, pois são enfileirados de forma persistente na tabela `print_jobs` com status `pending`.
    2.  Uma vez restabelecida a conectividade física da impressora de produção ou caixa, o driver reenvia automaticamente os jobs acumulados na ordem correta de solicitação.
