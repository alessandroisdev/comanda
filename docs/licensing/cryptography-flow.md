# FLUXO CRIPTOGRÁFICO DE LICENCIAMENTO — COMANDA

Este documento especifica os detalhes de segurança, criptografia, assinatura digital e isolamento físico utilizados no sistema de licenças do **Comanda**.

---

## 1. Premissa Comercial e Isolamento Físico
O Comanda **não é SaaS**. Cada cliente possui uma instalação física isolada.
* **Isolamento de Banco de Dados:** O painel gerenciador (`manager`) opera sob um banco de dados totalmente independente (`comanda_manager`), garantindo que nenhuma tabela de licenças, chaves privadas ou metadados de emissão comercial se misture com a aplicação principal (`www`), que roda na base `comanda`.
* **Resiliência Offline-First:** O controle de ativações, módulos permitidos e datas de expiração é validado localmente na aplicação principal de forma offline-first, sem necessidade de consultas externas frequentes à internet.

---

## 2. Par de Chaves Assimétricas (RSA-2048)
O licenciador (`manager`) e o sistema principal (`www`) comunicam-se via assinatura digital baseada em criptografia de chaves públicas/privadas (RSA de 2048 bits):

* **Chave Privada (exclusiva do `manager`):** Armazenada de forma restrita e segura pelo manager. É utilizada para assinar digitalmente o JSON de conteúdo da licença.
* **Chave Pública (distribuída em `www`):** Salva localmente em `storage/app/keys/license_public.key` nas instalações `www` dos clientes. Utilizada localmente para verificar a assinatura e garantir que o arquivo de licença não foi adulterado.

---

## 3. Estrutura do Arquivo de Licença
O arquivo de licença consiste em um payload JSON contendo as informações e a assinatura digital baseada no hash SHA-256:

```json
{
  "installation_uuid": "f3b392a8-129b-43d9-a9a3-a5c7c2512f45",
  "license_uuid": "d3b381a8-829b-43d9-a9a3-a5c7c2512a81",
  "client_uuid": "a8b381a8-129b-43d9-a9a3-a5c7c2512f22",
  "issued_at": "2026-06-01T12:00:00Z",
  "expires_at": "2027-06-01T12:00:00Z",
  "modules": [
    "admin",
    "api",
    "pdv",
    "waiter",
    "kitchen",
    "printing",
    "licensing"
  ],
  "signature": "base64_encoded_rsa_signature_string"
}
```

---

## 4. Algoritmo de Validação Local (www)
O `LicenseValidator` e `LicenseManager` realizam os seguintes passos na validação da licença:

1. **Leitura e Extração:** O arquivo de licença é lido e os campos de dados e a assinatura são separados.
2. **Serialização Canônica:** Os campos de dados são ordenados alfabeticamente por chaves (`ksort`) e codificados em JSON determinístico.
3. **Verificação da Assinatura:** Utilizando a chave pública RSA em `storage/app/keys/license_public.key`, a assinatura é verificada contra os dados canônicos usando `openssl_verify`.
4. **Verificação de Regras Temporais:** O validator checa se a data atual está entre `issued_at` e `expires_at`.
5. **Verificação do UUID de Instalação:** O validator certifica-se de que o `installation_uuid` gravado na licença confere exatamente com o UUID gravado em `storage/app/installation_uuid` da máquina local.
6. **Mapeamento de Módulos:** O `ModuleAccessService` cruza os módulos licenciados no payload com as requisições de acesso do sistema, liberando ou bloqueando rotas, menus e APIs de forma estrita.

---

## 5. Validação de Testes Automatizados
Durante a execução de testes automatizados (`php artisan test`), a suite do PHPUnit 12 lê dinamicamente as chaves físicas de desenvolvimento geradas no storage. Se presentes, os testes geram assinaturas RSA autênticas para validar cenários de expiração e caminhos de erro com máxima fidelidade criptográfica ao ambiente de produção.
