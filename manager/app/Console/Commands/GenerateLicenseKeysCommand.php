<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Licensing\KeyGeneratorService;
use Illuminate\Console\Command;

class GenerateLicenseKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate-keys {--force : Sobrescrever chaves existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o par de chaves criptográficas RSA-2048 para assinatura e validação de licenças offline';

    /**
     * Execute the console command.
     */
    public function handle(KeyGeneratorService $keyGenerator): int
    {
        $this->info('🔑 Gerando par de chaves criptográficas RSA-2048...');

        try {
            $force = $this->option('force');
            $keys = $keyGenerator->generate((bool) $force);

            $this->info('✅ Chaves geradas com sucesso!');
            $this->line('');
            $this->comment('Chave Privada salva em: storage/app/keys/license_private.key (Mantenha em segredo absoluto!)');
            $this->comment('Chave Pública salva em:  storage/app/keys/license_public.key');
            $this->line('');
            
            $this->info('👉 Copie a Chave Pública para o diretório do Cliente para habilitar a validação offline:');
            $this->line('   No seu terminal principal, execute:');
            $this->line('   cp manager/storage/app/keys/license_public.key www/storage/app/keys/license_public.key');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
