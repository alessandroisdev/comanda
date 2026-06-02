const http = require('http');

const SERVER_URL = 'http://localhost:8000';
const CHANNEL = 'admin.dashboard';

function runBenchmark(clientCount) {
    return new Promise((resolve) => {
        console.log(`\n=== INICIANDO BENCHMARK COM ${clientCount} CLIENTES SSE ===`);
        
        let connectedCount = 0;
        let messageReceivedCount = 0;
        let errorsCount = 0;
        const clients = [];
        const latencies = [];
        
        let startTime = null;

        // 1. Conectar os clientes
        for (let i = 0; i < clientCount; i++) {
            const req = http.get(`${SERVER_URL}/sse/stream/${CHANNEL}`, (res) => {
                if (res.statusCode !== 200) {
                    errorsCount++;
                    return;
                }
                
                connectedCount++;
                if (connectedCount === clientCount) {
                    // Quando todos conectarem, dispara a publicação
                    setTimeout(triggerPublish, 1000);
                }

                res.on('data', (chunk) => {
                    const text = chunk.toString();
                    // Se for a mensagem publicada de teste
                    if (text.includes('bench_test') && startTime) {
                        const endTime = process.hrtime(startTime);
                        const latencyMs = (endTime[0] * 1000) + (endTime[1] / 1000000);
                        latencies.push(latencyMs);
                        messageReceivedCount++;
                        
                        if (messageReceivedCount === clientCount) {
                            endBenchmark();
                        }
                    }
                });
            });

            req.on('error', (e) => {
                errorsCount++;
            });

            clients.push(req);
        }

        // 2. Publicar a mensagem
        function triggerPublish() {
            console.log(`  - Todos os ${clientCount} clientes conectados. Disparando evento...`);
            startTime = process.hrtime();
            
            const payload = JSON.stringify({
                channel: CHANNEL,
                event: 'bench_test',
                data: { timestamp: Date.now() }
            });

            const req = http.request({
                hostname: 'localhost',
                port: 8000,
                path: '/sse/publish',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Content-Length': Buffer.byteLength(payload)
                }
            }, (res) => {
                res.resume();
            });

            req.on('error', (e) => {
                console.error('Erro ao publicar:', e.message);
                errorsCount++;
            });

            req.write(payload);
            req.end();
            
            // Timeout de segurança se as mensagens não chegarem em 5 segundos
            setTimeout(() => {
                if (messageReceivedCount < clientCount) {
                    console.log(`  - [TIMEOUT] Apenas ${messageReceivedCount}/${clientCount} receberam a tempo.`);
                    endBenchmark();
                }
            }, 5000);
        }

        // 3. Encerrar
        function endBenchmark() {
            clients.forEach(c => c.destroy());
            
            // Obter uso de memória/CPU
            const memoryUsage = process.memoryUsage();
            const avgLatency = latencies.length > 0 ? (latencies.reduce((a, b) => a + b, 0) / latencies.length) : 0;
            
            console.log(`Resultados para ${clientCount} conexões:`);
            console.log(`  - Conexões Estabelecidas: ${connectedCount}/${clientCount}`);
            console.log(`  - Mensagens Recebidas: ${messageReceivedCount}/${clientCount}`);
            console.log(`  - Latência Média: ${avgLatency.toFixed(2)} ms`);
            console.log(`  - Erros / Timeouts: ${errorsCount}`);
            console.log(`  - Memória Usada (Runner RSS): ${(memoryUsage.rss / 1024 / 1024).toFixed(2)} MB`);
            
            resolve({
                clientCount,
                connectedCount,
                messageReceivedCount,
                avgLatency,
                errorsCount
            });
        }
    });
}

async function runAll() {
    console.log("=== INICIANDO BENCHMARK CONCORRENTE SSE DEDICADO (PORTA 8000) ===");
    
    const res20 = await runBenchmark(20);
    const res50 = await runBenchmark(50);
    const res100 = await runBenchmark(100);
    
    console.log("\n=== COMPARAÇÃO FINAL DE DESEMPENHO ===");
    console.log("Conexões | Latência Média | Erros | Status");
    [res20, res50, res100].forEach(r => {
        const status = r.errorsCount === 0 && r.messageReceivedCount === r.clientCount ? "✅ OK (0 erros)" : "❌ Falha";
        console.log(`${r.clientCount.toString().padEnd(8)} | ${r.avgLatency.toFixed(2).padEnd(11)} ms | ${r.errorsCount.toString().padEnd(5)} | ${status}`);
    });
}

runAll();
