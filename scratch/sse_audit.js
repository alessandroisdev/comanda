const http = require('http');

const sseUrl = 'http://localhost:8000/sse/stream/admin.dashboard';
const healthUrl = 'http://localhost:8000/api/health/live';
const numConnections = 20;

function connectSSE(id) {
    return new Promise((resolve) => {
        let resolved = false;
        
        const req = http.request(sseUrl, (res) => {
            let connected = false;
            
            res.on('data', (chunk) => {
                const text = chunk.toString();
                if (text.includes('connection.established') && !connected) {
                    connected = true;
                    if (!resolved) {
                        resolved = true;
                        resolve({ id, success: true, res, req });
                    }
                }
            });
            
            res.on('end', () => {
                if (!resolved) {
                    resolved = true;
                    resolve({ id, success: false, error: 'Conexão encerrada pelo servidor' });
                }
            });
        });
        
        req.setTimeout(5000, () => {
            if (!resolved) {
                resolved = true;
                req.destroy();
                resolve({ id, success: false, error: 'Timeout de 5 segundos' });
            }
        });
        
        req.on('error', (e) => {
            if (!resolved) {
                resolved = true;
                resolve({ id, success: false, error: e.message });
            }
        });
        
        req.end();
    });
}

function makeHealthRequest() {
    return new Promise((resolve) => {
        let resolved = false;
        const start = Date.now();
        const req = http.request(healthUrl, (res) => {
            let body = '';
            res.on('data', (chunk) => body += chunk);
            res.on('end', () => {
                if (!resolved) {
                    resolved = true;
                    resolve({
                        status: res.statusCode,
                        timeMs: Date.now() - start,
                        body: body
                    });
                }
            });
        });
        
        req.setTimeout(5000, () => {
            if (!resolved) {
                resolved = true;
                req.destroy();
                resolve({
                    status: 504,
                    timeMs: Date.now() - start,
                    error: 'Gateway Timeout (5 segundos)'
                });
            }
        });
        
        req.on('error', (e) => {
            if (!resolved) {
                resolved = true;
                resolve({
                    status: 500,
                    timeMs: Date.now() - start,
                    error: e.message
                });
            }
        });
        
        req.end();
    });
}

async function main() {
    console.log('=== AUDITORIA DE SSE E ESCALABILIDADE (ETAPA P4) ===\n');
    
    console.log('1. Medindo tempo de resposta inicial da API com 0 conexões SSE ativas...');
    const initRes = await makeHealthRequest();
    console.log(`Tempo: ${initRes.timeMs} ms | Status: ${initRes.status}\n`);
    
    console.log(`2. Abrindo ${numConnections} conexões SSE paralelas persistentes...`);
    const promises = [];
    for (let i = 0; i < numConnections; i++) {
        promises.push(connectSSE(i));
    }
    
    const results = await Promise.all(promises);
    const successfulConns = results.filter(r => r.success);
    const failedConns = results.filter(r => !r.success);
    console.log(`Conexões SSE ativas estabelecidas com sucesso: ${successfulConns.length}/${numConnections}`);
    if (failedConns.length > 0) {
        console.log(`Erros de conexão (amostra do primeiro erro): ${failedConns[0].error || 'Desconhecido'}\n`);
    } else {
        console.log('\n');
    }
    
    console.log('3. Disparando requisição comum para API de integridade enquanto SSE está ativo...');
    const startHealth = Date.now();
    const activeRes = await makeHealthRequest();
    console.log(`Tempo de resposta sob carga: ${activeRes.timeMs} ms | Status: ${activeRes.status}`);
    if (activeRes.error) {
        console.log(`Erro observado: ${activeRes.error}`);
    } else {
        console.log(`Corpo: ${activeRes.body}`);
    }
    console.log('');
    
    console.log('4. Encerrando conexões SSE...');
    for (const conn of successfulConns) {
        conn.req.destroy();
    }
    console.log('Conexões SSE fechadas.\n');
    
    // Comparação de impacto
    console.log('--- Comparação de Impacto de Escalabilidade ---');
    console.log(`Tempo de resposta inicial: ${initRes.timeMs} ms`);
    console.log(`Tempo de resposta sob SSE concorrente: ${activeRes.timeMs} ms`);
    
    if (activeRes.timeMs > initRes.timeMs * 3 || activeRes.status !== 200) {
        console.log('⚠️ RISCO DE ESCALABILIDADE CONFIRMADO: SSE concorrente bloqueia workers do PHP-FPM.');
    } else {
        console.log('✔ Workers do PHP-FPM escalam corretamente (Sem bloqueio).');
    }
}

main().catch(console.error);
