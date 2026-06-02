#!/bin/sh
set -e

# Criar links simbólicos no manager-app para compatibilidade com supervisord.conf compartilhado
if [ ! -f "/var/www/artisan" ] && [ -d "/var/www/manager" ]; then
    echo "Criando links simbólicos para compatibilidade do Supervisor no Manager..."
    ln -sf /var/www/manager/artisan /var/www/artisan
    ln -sf /var/www/manager/storage /var/www/storage
fi

echo "Ajustando permissões de diretórios..."
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

if [ -d "/var/www/manager" ]; then
    chmod -R 775 /var/www/manager/storage /var/www/manager/bootstrap/cache 2>/dev/null || true
    chown -R www-data:www-data /var/www/manager/storage /var/www/manager/bootstrap/cache 2>/dev/null || true
fi

echo "Permissões aplicadas com sucesso!"
