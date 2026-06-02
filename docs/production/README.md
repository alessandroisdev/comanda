# Hardening Operacional e Guia de Produção

Este guia descreve as configurações obrigatórias e boas práticas de segurança (hardening) para a execução do **Comanda** em ambientes de produção real.

## 🔒 1. Segurança de Tráfego e Cabeçalhos HTTP

O `SecurityHeadersMiddleware` injeta em todas as requisições de resposta cabeçalhos rígidos de proteção, garantindo conformidade com padrões OWASP e privacidade LGPD:

*   **HSTS (Strict-Transport-Security)**: Força o uso de HTTPS nativo:
    `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
*   **X-Frame-Options**: Impede ataques de Clickjacking:
    `X-Frame-Options: DENY`
*   **X-XSS-Protection**: Habilita a proteção do navegador contra ataques XSS:
    `X-XSS-Protection: 1; mode=block`
*   **X-Content-Type-Options**: Previne Sniffing de MIME-type:
    `X-Content-Type-Options: nosniff`
*   **Referrer-Policy**: Protege o vazamento de caminhos em referências cruzadas:
    `Referrer-Policy: strict-origin-when-cross-origin`
*   **CSP (Content Security Policy)**: Controla scripts e mídias permitidas contra injeções.

---

## 🚦 2. Rate Limiting e Proteção Brute Force

Todas as rotas de autenticação, checkout e APIs possuem limites rígidos de requisições configurados no Laravel Rate Limiter:

*   **Autenticação (`/auth/login`)**: Máximo de **5 tentativas por minuto** por IP/Email. Bloqueio automático de brute force temporário por 15 minutos se excedido.
*   **Rotas de API Gerais**: Limite dinâmico configurado de **60 requisições por minuto** por IP de cliente.
*   **Checkout de Delivery**: Limite de **5 requisições de compra por minuto** por IP/Cliente para evitar fraudes ou spam de gateway.

---

## ⚙️ 3. Configurações do Nginx e Docker

### Configuração de Stream Persistente (Nginx)
Para suportar o real-time via Server-Sent Events (SSE) sem gargalos, as diretivas do Nginx estão configuradas em `.docker/nginx/default.conf`:

```nginx
proxy_set_header Connection '';
proxy_http_version 1.1;
chunked_transfer_encoding off;
proxy_buffering off;
proxy_cache off;
read_timeout 600s;
```

### Supervisor (PHP Worker Daemon)
Para garantir que a fila Redis (`queue:work`) e o SSE rodem de forma ininterrupta, o container PHP-FPM utiliza o daemon Supervisor (`supervisord.conf`), reiniciando os workers automaticamente se caírem.
