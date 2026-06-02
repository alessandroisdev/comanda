# Restauração e Recuperação de Dados (Restore)

O subsistema de backup do **Comanda** conta com uma rotina automatizada e auditada para a restauração completa dos dados e arquivos de mídia.

## 🛡️ Fluxo de Validação de Integridade

O processo de restore garante que nenhum backup corrompido ou adulterado seja injetado no sistema principal.

1.  **Validação de Checksum**: O checksum SHA-256 do arquivo físico presente no storage é calculado e comparado com o valor armazenado na base de dados (calculado no momento de criação do backup). Caso divirja, a operação é sumariamente interrompida para evitar corrupção lógica de banco.
2.  **Descriptografia Simétrica**: O arquivo é descriptografado usando a chave definida no `.env` (`BACKUP_ENCRYPTION_KEY`). Se a chave estiver incorreta, o processo falha lançando exceção específica.
3.  **Descompactação de Dump e Arquivos**: O arquivo ZIP principal é extraído temporariamente em `storage/app/backup_temp/`.
4.  **Importação de Banco (MySQL)**: O dump SQL (`db_backup.sql`) é importado de forma transacional.
5.  **Substituição de Mídias (Storage)**: A pasta de uploads do storage público (`storage/app/public`) é substituída pelos arquivos armazenados em `storage_backup.zip`.
6.  **Limpeza**: O diretório temporário `backup_temp` é limpo fisicamente, mesmo em caso de erro.
7.  **Registro de Auditoria**: Toda operação de restauração com sucesso ou falha é gravada de forma imutável com o correlation ID correspondente e logada no `AuditLogService` nos logs estruturados.

---

## 🛠️ Execução da Restauração via CLI

Para restaurar um backup específico, você pode usar o comando Artisan passando o ID do backup desejado (conforme listado no banco de dados):

```bash
php artisan comanda:backup:restore {backup_id}
```

> [!WARNING]
> A execução da restauração de banco e storage substituirá integralmente os dados correntes da instalação. Recomenda-se realizar um backup de segurança imediato antes de prosseguir com o restore.
