<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateRsaKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licensing:generate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $signature_description = 'Gera o par de chaves assimétricas RSA (2048 bits) para a assinatura e verificação de licenças.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando a geração do par de chaves RSA de 2048 bits...');

        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // Criar a chave privada e pública
        $res = openssl_pkey_new($config);

        if (! $res) {
            $this->error('Falha ao gerar o par de chaves RSA do OpenSSL. Verifique a configuração do PHP.');

            return 1;
        }

        // Extrair chave privada
        openssl_pkey_export($res, $privateKey);

        // Extrair chave pública
        $publicKeyDetails = openssl_pkey_get_details($res);
        $publicKey = $publicKeyDetails['key'];

        // Garantir diretório storage/app/keys/
        $keysDir = storage_path('app/keys');
        if (! is_dir($keysDir)) {
            mkdir($keysDir, 0755, true);
        }

        file_put_contents($keysDir.'/license_private.key', $privateKey);
        file_put_contents($keysDir.'/license_public.key', $publicKey);

        $this->info("Chaves assimétricas geradas com sucesso em: {$keysDir}");
        $this->line('- Chave Privada: license_private.key (Mantenha em sigilo absoluto!)');
        $this->line('- Chave Pública: license_public.key (Será distribuída com as instalações)');

        return 0;
    }
}
