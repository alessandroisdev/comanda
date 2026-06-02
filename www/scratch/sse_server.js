const http = require('http');

// Armazena as conexões por canal: { [channel]: Set<res> }
const channels = {};

// Função para enviar dados no formato SSE
function sendSSE(res, event, data) {
    res.write(`event: ${event}\n`);
    res.write(`data: ${JSON.stringify(data)}\n\n`);
}

const server = http.createServer((req, res) => {
    // Configura os headers de CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const url = new URL(req.url, `http://${req.headers.host || 'localhost'}`);
    const pathname = url.pathname;

    // 1. GET /health ou /sse/health
    if (pathname === '/health' || pathname === '/sse/health') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        let totalConns = 0;
        Object.keys(channels).forEach(ch => {
            totalConns += channels[ch].size;
        });
        res.end(JSON.stringify({
            status: 'ok',
            active_channels: Object.keys(channels).length,
            active_connections: totalConns
        }));
        return;
    }

    // 2. POST /publish ou /sse/publish
    if ((pathname === '/publish' || pathname === '/sse/publish') && req.method === 'POST') {
        let body = '';
        req.on('data', chunk => {
            body += chunk;
        });
        req.on('end', () => {
            try {
                const payload = JSON.parse(body);
                const { channel, event, data } = payload;
                if (!channel || !event) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: 'Missing channel or event' }));
                    return;
                }

                const clients = channels[channel];
                if (clients && clients.size > 0) {
                    clients.forEach(clientRes => {
                        sendSSE(clientRes, event, data || {});
                    });
                }

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, clients_notified: clients ? clients.size : 0 }));
            } catch (err) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'Invalid JSON' }));
            }
        });
        return;
    }

    // 3. GET /sse/stream/:channel ou /sse/:channel
    const streamRegex = /^\/sse\/(?:stream\/)?([a-zA-Z0-9._-]+)$/;
    const match = pathname.match(streamRegex);
    if (match && req.method === 'GET') {
        const channel = match[1];

        // Headers para manter a conexão SSE aberta
        res.writeHead(200, {
            'Content-Type': 'text/event-stream',
            'Cache-Control': 'no-cache, no-transform, private',
            'Connection': 'keep-alive',
            'X-Accel-Buffering': 'no' // Impede buffering do Nginx
        });

        // Envia ping inicial para estabelecer a conexão
        res.write(':ok\n\n');

        if (!channels[channel]) {
            channels[channel] = new Set();
        }
        channels[channel].add(res);

        console.log(`[SSE] Cliente conectado ao canal: ${channel}. Total no canal: ${channels[channel].size}`);

        // Trata desconexão do cliente
        req.on('close', () => {
            if (channels[channel]) {
                channels[channel].delete(res);
                console.log(`[SSE] Cliente desconectado do canal: ${channel}. Restantes: ${channels[channel].size}`);
                if (channels[channel].size === 0) {
                    delete channels[channel];
                }
            }
        });
        return;
    }

    // Rota fallback
    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Not Found' }));
});

// Envia heartbeat (keepalive) a cada 15 segundos para evitar timeouts do Nginx/browser
setInterval(() => {
    Object.keys(channels).forEach(channel => {
        channels[channel].forEach(res => {
            res.write(':keepalive\n\n');
        });
    });
}, 15000);

const PORT = 8082;
server.listen(PORT, '0.0.0.0', () => {
    console.log(`[SSE Server] Rodando na porta ${PORT}`);
});
