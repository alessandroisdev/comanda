# DIRETRIZES E PADRÕES DE QUALIDADE — COMANDA

Este documento reúne os padrões de qualidade de código, análise estática e diretrizes de testes automatizados adotados no ecossistema do **Comanda**.

---

## 1. Análise Estática com PHPStan
Para garantir a integridade dos dados e a prevenção de bugs em tempo de compilação, o Comanda utiliza o analisador estático **PHPStan**.

* **Nível de Análise:** Configurado no **Nível 5** de forma estrita.
* **Escopo:** Cobre 100% das pastas `app/`, `config/`, `database/`, `routes/` (exceto console.php devido a binds dinâmicos) e `tests/`.
* **Comando de Execução:**
  ```bash
  composer analyse
  ```
  *(Internamente executa `vendor/bin/phpstan analyse --memory-limit=1G`)*
* **Regras de Ouro:**
  * Proibido usar propriedades ou variáveis não tipadas em métodos novos.
  * Todo Model Eloquent exposto deve ter PHPDoc indicando suas propriedades dinâmicas do banco (ex: `@property string $uuid`).
  * Em controladores e serviços, priorizar o retorno explícito de tipos.

---

## 2. Testes Automatizados com PHPUnit 12
Todos os serviços cruciais de segurança, licenciamento, auditoria e realtime possuem testes de feature e unitários.

* **Framework de Testes:** PHPUnit 12.x rodando sob PHP 8.4+.
* **Padrão de Atributos:** Todos os métodos de teste devem usar o atributo nativo do PHP 8+ `#[PHPUnit\Framework\Attributes\Test]` e importar `use PHPUnit\Framework\Attributes\Test;`. A anotação `@test` em PHPDoc está depreciada e proibida.
* **Comando de Execução:**
  ```bash
  php artisan test
  ```
* **Áreas de Cobertura Crítica da Fundação:**
  * **Licenciamento (`LicenseValidator`, `LicenseManager`):** Garante a validade criptográfica (RSA-2048) e temporal da assinatura.
  * **Módulos (`ModuleAccessService`):** Valida a restrição física de acesso comercial com base na licença cacheada.
  * **Auditoria (`AuditService`):** Garante a persistência de logs de ações críticas estruturados em banco.
  * **SSE e Telemetria (`HealthCheckController`, `/sse/test`):** Inspeciona a resposta de headers e status físico do sistema.

---

## 3. Formatação e Estilo com Laravel Pint
A consistência visual de escrita do código é mantida pelo Pint.

* **Padrão de Estilo:** PSR-12 estrito com regras modernas do Laravel.
* **Comando de Execução (Modo Teste):**
  ```bash
  vendor/bin/pint --test
  ```

---

## 4. Segurança do Frontend (NPM Audit & Typecheck)
* **Compilação Local:** Todo ativo compilado pelo Vite usa pacotes locais. Nenhuma CDN externa é permitida.
* **Typecheck (TypeScript):** Garantido pelo compilador TypeScript `tsc --noEmit`.
* **Comando de Execução:**
  ```bash
  npm run typecheck
  npm run build
  npm audit
  ```
