#!/bin/sh
set -e

# Esperar o banco MySQL estar disponível
/usr/local/bin/wait-for-mysql.sh mysql

# Ajustar permissões
/usr/local/bin/permissions.sh

# Executar migrações se em produção/homologação
if [ -f "artisan" ]; then
    echo "Executando migrações e otimizações..."
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Iniciar o supervisor para gerenciar os workers/cron
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
