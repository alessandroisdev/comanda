# FLUXO CRIPTOGRÁFICO DE LICENCIAMENTO — COMANDA

Este documento especifica os detalhes de segurança, criptografia e assinatura digital utilizados no sistema de licenças do **Comanda**.

## 1. Premissa Comercial
O Comanda **não é SaaS**. Cada cliente possui uma instalação física isolada. O controle de ativações, módulos permitidos e datas de expiração é validado localmente de forma resiliente e offline-first.

---

## 2. Par de Chaves Assimétricas (RSA)
O licenciador (`manager`) e o sistema principal (`www`) se comunicam via assinatura digital baseada em criptografia de chaves públicas/privadas (RSA de 2048 bits):

* **Chave Privada (armazenada exclusivamente no `manager`):** Utilizada pelo gerenciador de licenças para assinar digitalmente o JSON de conteúdo da licença.
* **Chave Pública (distribuída em todas as instalações `www`):** Utilizada localmente para verificar a autenticidade da licença e garantir que o arquivo de licença não foi adulterado.

---

## 3. Estrutura do Arquivo de Licença
O arquivo de licença consiste em um payload JSON serializado e codificado contendo as informações e a assinatura:

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

1. **Leitura e Extração:** O arquivo de licença (ou string) é lido. Os campos de dados e a assinatura são separados.
2. **Serialização Canônica:** Os campos de dados são serializados de forma canônica para garantir determinismo.
3. **Verificação da Assinatura:** Utilizando a chave pública RSA armazenada localmente (geralmente em `storage/app/keys/license_public.key` ou via variável de ambiente), a assinatura é verificada contra os dados canônicos.
4. **Verificação de Regras Temporais:** O validator checa se a data atual está entre `issued_at` e `expires_at`.
5. **Verificação do UUID de Instalação:** O validator certifica-se de que o `installation_uuid` gravado na licença confere exatamente com o UUID gerado localmente na instalação do cliente.
6. **Mapeamento de Módulos:** O `ModuleAccessService` cruza os módulos licenciados no payload com as requisições de acesso do sistema, liberando ou bloqueando rotas, menus e APIs.
