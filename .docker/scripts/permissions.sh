#!/bin/sh
set -e

echo "Ajustando permissões de diretórios..."
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

if [ -d "/var/www/manager" ]; then
    chmod -R 775 /var/www/manager/storage /var/www/manager/bootstrap/cache 2>/dev/null || true
    chown -R www-data:www-data /var/www/manager/storage /var/www/manager/bootstrap/cache 2>/dev/null || true
fi

echo "Permissões aplicadas com sucesso!"
