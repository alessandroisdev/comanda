FROM php:8.4-fpm-alpine

# Instalar o utilitário php-extension-installer para downloads de extensões pré-compiladas estáveis
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Instalar dependências básicas do sistema
RUN apk update && apk add --no-cache \
    zip \
    unzip \
    git \
    supervisor \
    bash \
    mysql-client

# Instalar extensões PHP de forma performática e sem compilação local pesada
RUN install-php-extensions pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache redis

# Obter Composer mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www

# Copiar arquivos de configuração a partir do contexto do build (.docker)
COPY app/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY app/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar ponto de entrada a partir do contexto do build (.docker)
COPY scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]
