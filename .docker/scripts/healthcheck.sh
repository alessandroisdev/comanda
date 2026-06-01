#!/bin/sh
set -e

# Testar se PHP-FPM está ativo
if ! pgrep php-fpm > /dev/null; then
    echo "PHP-FPM inativo"
    exit 1
fi

echo "Containers operacionais!"
exit 0
