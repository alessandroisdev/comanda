# DOCUMENTAÇÃO — REALTIME & REATIVIDADE (SERVER-SENT EVENTS)

O ecossistema Comanda utiliza a tecnologia **Server-Sent Events (SSE)** como padrão oficial de reatividade e realtime, abolindo qualquer forma de polling e websocket complexo.

---

## 1. Funcionamento Técnico

SSE fornece uma conexão unidirecional leve e persistente de HTTP (`text/event-stream`) entre o servidor e o navegador do cliente.
* **`SseQueueService`**: Enfileira dinamicamente os eventos no Cache/Redis.
* **`SseController`**: Conecta via rota `/sse/stream/{channel}` e lê de forma persistente as mensagens publicadas, enviando heartbeats a cada 15 segundos para evitar perda de conexão.

---

## 2. Canais e Eventos Padronizados

* **`admin.tables`** (Canal de Mesas):
  * `tables.created`
  * `tables.updated`
  * `tables.status_changed`
* **`admin.sessions`** (Canal de Comandas):
  * `session.opened`
  * `session.closed`
  * `session.cancelled`
  * `session.transferred`
  * `session.merged`
* **`admin.orders`** (Canal de Pedidos):
  * `orders.created`
  * `orders.updated`
  * `orders.cancelled`
  * `orders.sent_to_kitchen`
  * `orders.ready`
* **`admin.kitchen`** (Canal da Cozinha):
  * `kitchen.created`
  * `kitchen.preparing`
  * `kitchen.ready`
  * `kitchen.completed`

---

## 3. Benefícios da Tecnologia

* **Conformidade com Proxies:** Roda nativamente na porta 80/443 por HTTP clássico, contornando bloqueios de rede comuns a WebSockets.
* **Baixo Consumo de Recursos:** Não mantém processos de servidores de socket pesados abertos na máquina.
