# Backup Corporativo

O **Comanda** conta com uma solução nativa de backup de dados integrada, permitindo salvaguardar tanto o banco de dados (MySQL/MariaDB) quanto os arquivos de storage do usuário (como fotos de produtos e layouts).

## 📁 Estrutura de Armazenamento e Criptografia

Os backups são empacotados em arquivos ZIP criptografados com chave simétrica usando o algoritmo **AES-256-CBC**.
O arquivo resultante contém:
*   `db_backup.sql`: Dump completo do banco de dados relacional.
*   `storage_backup.zip`: Cópia compactada do diretório de uploads do storage público (`storage/app/public`).

Os arquivos de backup gerados ficam no diretório `storage/app/backups/` e possuem a extensão `.zip.enc` para garantir que o conteúdo não seja legível sem a respectiva chave de criptografia.

---

## ⚙️ Configuração da Chave de Criptografia

A chave simétrica usada na criptografia/descriptografia deve possuir pelo menos 32 caracteres (256 bits) e ser definida no arquivo `.env` da aplicação:

```env
BACKUP_ENCRYPTION_KEY="sua_chave_secreta_de_32_caracteres"
```

> [!CAUTION]
> Caso a chave `BACKUP_ENCRYPTION_KEY` seja perdida, será impossível descriptografar e restaurar qualquer backup gerado. Guarde esta chave em um gerenciador de segredos seguro (ex: HashiCorp Vault, AWS Secrets Manager ou Azure Key Vault).

---

## 🛠️ Execução de Backups via CLI

Você pode executar o backup manualmente a qualquer momento usando o comando Artisan:

```bash
php artisan comanda:backup:run
```

O comando realiza o dump do banco, compacta a pasta de uploads, aplica a criptografia simétrica AES-256-CBC, gera o checksum SHA-256 para controle de integridade, registra o backup na tabela `backups` no banco de dados e dispara a política de retenção.

---

## ⏳ Política de Retenção Automática

Para evitar o esgotamento do espaço em disco no servidor, o `BackupService` executa automaticamente uma rotina de expurgo ao final de cada backup.

*   **Regra de Retenção**: Backups com mais de **7 dias** são automaticamente deletados do storage físico e suas referências removidas do banco de dados.
*   Esta rotina garante que o servidor mantenha sempre uma janela móvel de 7 backups diários.
