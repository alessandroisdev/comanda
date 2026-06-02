const http = require('http');

const url = 'http://localhost:8000/api/v1/menu/products';

function makeRequest() {
    return new Promise((resolve) => {
        const start = process.hrtime();
        const u = new URL(url);
        const options = {
            hostname: u.hostname,
            port: u.port,
            path: u.pathname + u.search,
            method: 'GET'
        };

        const req = http.request(options, (res) => {
            let body = '';
            res.on('data', (chunk) => body += chunk);
            res.on('end', () => {
                const diff = process.hrtime(start);
                const timeMs = (diff[0] * 1000) + (diff[1] / 1000000);
                resolve({
                    status: res.statusCode,
                    timeMs: timeMs
                });
            });
        });

        req.on('error', (e) => {
            const diff = process.hrtime(start);
            const timeMs = (diff[0] * 1000) + (diff[1] / 1000000);
            resolve({
                status: 500,
                error: e.message,
                timeMs: timeMs
            });
        });

        req.end();
    });
}

function calculateMetrics(results) {
    const times = results.map(r => r.timeMs).sort((a, b) => a - b);
    const errors = results.filter(r => r.status !== 200).length;
    
    const sum = times.reduce((a, b) => a + b, 0);
    const avg = sum / times.length;
    const max = times[times.length - 1];
    
    // Percentis
    const p95Idx = Math.floor(times.length * 0.95);
    const p95 = times[p95Idx];
    
    const p99Idx = Math.floor(times.length * 0.99);
    const p99 = times[p99Idx];

    return {
        avg: avg.toFixed(2),
        max: max.toFixed(2),
        p95: p95.toFixed(2),
        p99: p99.toFixed(2),
        errors: errors
    };
}

async function runBenchmark(concurrency) {
    console.log(`Disparando ${concurrency} requisições simultâneas para ${url}...`);
    
    const startOverall = process.hrtime();
    const promises = [];
    for (let i = 0; i < concurrency; i++) {
        promises.push(makeRequest());
    }
    
    const results = await Promise.all(promises);
    const diffOverall = process.hrtime(startOverall);
    const overallTimeMs = (diffOverall[0] * 1000) + (diffOverall[1] / 1000000);
    
    const metrics = calculateMetrics(results);
    
    console.log(`--- Resultados para ${concurrency} Concorrentes ---`);
    console.log(`Tempo Total do Lote: ${overallTimeMs.toFixed(2)} ms`);
    console.log(`Tempo Médio: ${metrics.avg} ms`);
    console.log(`Tempo Máximo: ${metrics.max} ms`);
    console.log(`Percentil 95 (P95): ${metrics.p95} ms`);
    console.log(`Percentil 99 (P99): ${metrics.p99} ms`);
    console.log(`Erros: ${metrics.errors}`);
    console.log('-------------------------------------------\n');
    
    return metrics;
}

async function main() {
    console.log('=== BENCHMARK DE PERFORMANCE DA API (FASE 7) ===\n');
    
    // 1. Warm-up
    console.log('Executando warm-up de 5 requisições...');
    for (let i = 0; i < 5; i++) {
        await makeRequest();
    }
    console.log('Warm-up finalizado.\n');
    
    // 2. 100 concorrentes
    await runBenchmark(100);
    
    // 3. 500 concorrentes
    await runBenchmark(500);
}

main().catch(console.error);
