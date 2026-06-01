# ARQUITETURA DO PROJETO COMANDA

Este documento descreve a visão geral, a constituição técnica e as diretrizes de governança da arquitetura do ecossistema **Comanda**.

---

## 1. Visão Geral da Stack
O Comanda adota uma arquitetura de Monólito Modular com forte isolamento físico por instalação (sem compartilhamento de recursos ou banco entre diferentes clientes - Tenant Isolado / Não SaaS).

* **Backend Principal (www):** PHP 8.4+ / Laravel 13.x / MySQL 8.0 / Redis
* **Tempo Real (Realtime):** Server-Sent Events (SSE) nativo
* **Frontend Administrativo e Base:** Laravel Blade / Bootstrap 5 / jQuery / TypeScript e compilador Vite (100% locais, sem dependências de CDNs externas)
* **Qualidade Estática:** Análise rigorosa com PHPStan (Nível 5) e formatação com Laravel Pint (PSR-12)
* **Segurança e APIs:** OpenAPI 3.0.3 validada sem erros com Redocly CLI
* **Serviço de Licenciamento (manager):** Laravel 13.x isolado com banco dedicado (`comanda_manager`) e infraestrutura independente

---

## 2. Camadas da Aplicação (Backend)
O fluxo de execução segue rigorosamente o padrão de camadas de nível empresarial, proibindo lógica de negócios em controllers (que devem conter entre 20 e 50 linhas) e models:

```text
Request ──> Form Request ──> Controller ──> DTO ──> Service / Action ──> Model ──> Database
```

* **Http/Controllers:** Responsável apenas por receber a requisição, validar via Form Request, mapear para um DTO, repassar para o Service/Action e retornar um Resource em JSON ou renderizar a View.
* **Http/Requests (Form Requests):** Validação estrita de tipos e regras básicas de entradas de dados antes de atingir a lógica de negócios.
* **DTOs (Data Transfer Objects):** Transporte imutável e tipado de dados entre controladores e serviços.
* **Services:** Concentram 100% da lógica e regras de negócio da aplicação.
* **Actions:** Classes transacionais focadas em tarefas atômicas e altamente reutilizáveis de negócios.
* **Models:** Apenas representação de tabelas de banco de dados, relacionamentos Eloquent e scopes simples. Lógica financeira, autorização e complexidade de workflows são proibidas nesta camada.
* **Http/Resources:** Serialização estrita de saída para APIs. A exposição direta de instâncias de Models é proibida.
* **Enums & Value Objects:** Tipagem rica para fluxos e dados de negócios (ex: `Money` em centavos, `LicenseKey`, etc.).

---

## 3. Diretórios Principais
* `/.docker`: Provisionamento de containers PHP 8.4, Nginx com suporte SSE, MySQL e Redis.
* `/docs`: Especificações OpenAPI, arquitetura, implantação, segurança, licenciamento e qualidade.
* `/manager`: Painel gerenciador de licenças criptografadas (RSA-2048) operando em banco de dados isolado `comanda_manager`.
* `/www`: Core da aplicação contendo backend, painéis administrativos locais e APIs operacionais baseadas em TypeScript.

---

## 4. Governança e Regras Absolutas
* **Identidade Isolada:** Administradores (`users`), funcionários (`employees`) e clientes (`customers`) possuem tabelas e guards de autenticação totalmente separados.
* **IDs Públicos (UUID):** IDs incrementais do banco são exclusivamente para indexação e FKs internas. Qualquer comunicação externa, URLs ou payloads de API usam UUIDs públicos.
* **SSE Nativo:** Comunicação realtime exclusiva através do protocolo SSE. WebSockets e Polling são proibidos.
* **Sem CDNs Externas:** O frontend compila localmente via Vite todos os ativos de CSS/JS, garantindo independência de rede e segurança (XSS/CSP).
* **Análise Estática Contínua:** PHPStan rodando localmente no nível 5 garante a tipagem rigorosa de retornos de métodos, heranças e variáveis antes do deploy.
