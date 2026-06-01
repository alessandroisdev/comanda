# ARQUITETURA DO PROJETO COMANDA

Este documento descreve a visão geral e a constituição técnica da arquitetura do sistema **Comanda**.

## 1. Visão Geral da Stack
O sistema foi construído sob uma arquitetura de Monólito Modular isolado por instalação (sem compartilhamento de recursos entre tenants).

* **Backend Principal (www):** PHP 8.4+ / Laravel 13.x / MySQL / Redis
* **Tempo Real (Realtime):** Server-Sent Events (SSE) nativo
* **Frontend Administrativo:** Laravel Blade / Bootstrap 5 / Vanilla JS + TS
* **Frontends Operacionais:** ReactJS / TypeScript / Vite / SSE / i18next
* **Serviço de Licenciamento (manager):** Laravel 13.x (gerador de chaves criptográficas de licença)

---

## 2. Camadas da Aplicação (Backend)
O fluxo de execução segue rigorosamente o padrão de camadas estipulado, proibindo lógica de negócios em controllers e mantendo controllers "finos" (entre 20 e 50 linhas):

```text
Request ──> Form Request ──> Controller ──> DTO ──> Service / Action ──> Model ──> Database
```

* **Http/Controllers:** Responsável apenas por receber a requisição, validar via Form Request, mapear para um DTO, repassar para o Service e retornar um Resource estruturado em JSON ou renderizar a View.
* **Http/Requests (Form Requests):** Validação estrita de entradas antes que atinjam a camada de regras de negócio.
* **DTOs (Data Transfer Objects):** Transporte imutável de dados entre controladores e serviços.
* **Services:** Concentram 100% da lógica e regras de negócio da aplicação.
* **Actions:** Classes transacionais focadas em tarefas atômicas e reutilizáveis de negócios.
* **Models:** Apenas representação de dados, relacionamentos e scopes simples. Lógica financeira, autorização e complexidade de workflows são proibidas nesta camada.
* **Http/Resources:** Serialização estrita de saída para APIs. Exposição direta de Models é proibida.
* **Enums & Value Objects:** Tipagem rica para fluxos e dados de negócios (ex: `Money` em centavos, `LicenseKey`, etc.).

---

## 3. Diretórios Principais
* `/.docker`: Arquivos de provisionamento e infraestrutura dos containers.
* `/docs`: Documentações detalhadas de APIs, arquitetura, implantação, segurança e licenciamento.
* `/manager`: Aplicação isolada responsável pelo controle e geração de licenças.
* `/www`: Aplicação principal contendo backend, frontends React e APIs operacionais.

---

## 4. Governança e Regras Absolutas
* **Identidade Isolada:** Administradores (`users`), funcionários (`employees`) e clientes (`customers`) possuem tabelas e guards totalmente separados.
* **IDs Públicos:** IDs incrementais do banco são exclusivamente para indexação e FKs internas. Qualquer comunicação externa, URLs ou payloads de API usam UUIDs públicos.
* **SSE Nativo:** Comunicação realtime exclusiva através do protocolo SSE. WebSockets e Polling são proibidos.
* **Sem Livewire ou Alpine:** Componentes reativos operacionais usam React e Vite. Telas administrativas clássicas usam Blade e Bootstrap.
