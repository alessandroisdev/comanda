const http = require('http');
const fs = require('fs');
const path = require('path');

console.log("=== INICIANDO AUDITORIA FORENSE DE CONCORRENCIA SSE E PHP-FPM (BLOQUEIO 6) ===\n");

const SSE_URL = 'http://localhost:8000/sse/admin.orders';
const HTTP_TARGETS = [
    { name: 'Health Live', path: '/api/health/live' },
    { name: 'Menu Publico', path: '/api/v1/menu/categories' },
    { name: 'Delivery Frete', path: '/api/v1/delivery/frete?cep=01311000' }
];

async function runAudit() {
    const sseConnections = [];
    const connectionCounts = [20, 50, 100];
    let auditReport = "=== RESULTADO DA AUDITORIA FORENSE DE CONCORRENCIA SSE (BLOQUEIO 6) ===\n\n";

    for (const count of connectionCounts) {
        console.log(`\n--- FASE 1: Conectando ${count} clientes SSE simultaneos ---`);
        
        // 1. Abrir conexões SSE
        const pConns = [];
        for (let i = 0; i < count; i++) {
            pConns.push(new Promise((resolve, reject) => {
                const req = http.get(SSE_URL, (res) => {
                    // Mantem a conexao aberta e ativa
                    sseConnections.push(req);
                    resolve();
                });
                req.on('error', (err) => {
                    reject(err);
                });
                // Define timeout alto para nao fechar
                req.setTimeout(60000);
            }));
        }

        try {
            await Promise.all(pConns);
            console.log(`  - Sucesso: ${count} conexoes SSE persistentes abertas e ativas.`);
        } catch (err) {
            console.error("  - Erro ao abrir conexoes SSE:", err.message);
        }

        // 2. Enquanto as conexões SSE estão abertas, testar concorrência HTTP normal batendo no PHP-FPM
        console.log(`--- FASE 2: Disparando 50 requisicoes HTTP normais ao PHP-FPM sob carga ---`);
        const latencies = [];
        let errorCount = 0;

        const makeHttpRequest = (target) => {
            return new Promise((resolve) => {
                const start = process.hrtime();
                const req = http.get(`http://localhost:8000${target.path}`, (res) => {
                    let body = '';
                    res.on('data', chunk => body += chunk);
                    res.on('end', () => {
                        const diff = process.hrtime(start);
                        const ms = (diff[0] * 1e3) + (diff[1] / 1e6);
                        
                        if (res.statusCode >= 400) {
                            console.log(`    [ERRO] ${target.name} (${target.path}) retornou status HTTP ${res.statusCode}. Resposta: ${body.substring(0, 100)}`);
                            errorCount++;
                        } else {
                            latencies.push(ms);
                        }
                        resolve();
                    });
                });
                req.on('error', (err) => {
                    console.log(`    [FALHA] ${target.name} (${target.path}) falhou: ${err.message}`);
                    errorCount++;
                    resolve();
                });
                req.setTimeout(5000); // 5s timeout
            });
        };

        const httpRequests = [];
        for (let i = 0; i < 50; i++) {
            const target = HTTP_TARGETS[i % HTTP_TARGETS.length];
            httpRequests.push(makeHttpRequest(target));
        }

        await Promise.all(httpRequests);

        // 3. Processar métricas obtidas
        latencies.sort((a, b) => a - b);
        const total = latencies.reduce((acc, val) => acc + val, 0);
        const avg = latencies.length > 0 ? (total / latencies.length) : 0;
        const p95 = latencies.length > 0 ? latencies[Math.floor(latencies.length * 0.95)] : 0;
        const p99 = latencies.length > 0 ? latencies[Math.floor(latencies.length * 0.99)] : 0;

        console.log(`Resultados sob carga de ${count} clientes SSE:`);
        console.log(`  - Requisicoes normais com sucesso: ${latencies.length}/50`);
        console.log(`  - Requisicoes falhas (Erros / Timeouts): ${errorCount}`);
        console.log(`  - Latencia Media: ${avg.toFixed(2)} ms`);
        console.log(`  - Latencia P95: ${p95.toFixed(2)} ms`);
        console.log(`  - Latencia P99: ${p99.toFixed(2)} ms`);

        auditReport += `Carga SSE: ${count} conexoes concorrentes\n`;
        auditReport += `  - Requisicoes normais com sucesso: ${latencies.length}/50\n`;
        auditReport += `  - Erros / Timeouts (HTTP 504/500): ${errorCount}\n`;
        auditReport += `  - Latencia Media: ${avg.toFixed(2)} ms\n`;
        auditReport += `  - Latencia P95: ${p95.toFixed(2)} ms\n`;
        auditReport += `  - Latencia P99: ${p99.toFixed(2)} ms\n`;
        auditReport += `  - Status do Bloco: ${errorCount === 0 ? "PASS (FPM livre e rapido)" : "FAIL (Timeout/FPM bloqueado)"}\n\n`;

        // 4. Fechar as conexões SSE abertas nesta fase
        while (sseConnections.length > 0) {
            const conn = sseConnections.pop();
            conn.destroy();
        }
        console.log(`  - Conexoes SSE fechadas e limpas.`);
    }

    fs.writeFileSync(path.join(__dirname, 'sse_concurrency_audit_result.txt'), auditReport);
    console.log("\nAuditoria concluida e salva em scratch/sse_concurrency_audit_result.txt");
}

runAudit();
