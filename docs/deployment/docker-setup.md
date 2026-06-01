# GUIA DE IMPLANTAÇÃO DOCKER — COMANDA

Este documento descreve os passos necessários para configurar e subir o ambiente do **Comanda** utilizando Docker.

## 1. Estrutura de Containers
O ambiente é composto pelos seguintes containers:

1. **comanda-app (`app`):** PHP 8.4-fpm executando a aplicação principal (`www`).
2. **comanda-manager-app (`manager-app`):** PHP 8.4-fpm executando a aplicação gerenciadora de licenças (`manager`).
3. **comanda-nginx (`nginx`):** Servidor web Nginx expondo as portas 8000 (www) e 8080 (manager).
4. **comanda-mysql (`mysql`):** Banco de dados MySQL 8.0.
5. **comanda-redis (`redis`):** Serviço Redis para gerenciamento de cache, filas e suporte de realtime.

---

## 2. Requisitos Prévios
Certifique-se de que possui instalado na máquina host:
* Docker Engine 20.10+
* Docker Compose 2.0+

---

## 3. Passo a Passo para Subir o Ambiente

### Passo 1: Inicializar arquivos de configuração do ambiente (.env)
Crie os arquivos `.env` copiando de seus respectivos exemplos na pasta `/www` e `/manager` (assim que criados).

### Passo 2: Executar o Docker Compose
Na raiz do projeto (`c:\MeusSites\alessandroisdev\comanda`), execute:
```bash
docker compose up -d --build
```

### Passo 3: Configurar dependências e permissões do Laravel
Acesse os containers para rodar o composer e os comandos artisan:

Para o app principal (`www`):
```bash
docker exec -it comanda-app composer install
docker exec -it comanda-app php artisan key:generate
docker exec -it comanda-app php artisan migrate --seed
```

Para o app de licenças (`manager`):
```bash
docker exec -it comanda-manager-app composer install
docker exec -it comanda-manager-app php artisan key:generate
docker exec -it comanda-manager-app php artisan migrate --seed
```

---

## 4. Otimizações de Rede e SSE no Nginx
A configuração do Nginx no arquivo `.docker/nginx/default.conf` desativa expressamente o buffering do fastcgi para conexões com a extensão `.php` no container principal:
```nginx
fastcgi_buffering off;
fastcgi_read_timeout 600s;
```
Isso é obrigatório para que os pacotes do Server-Sent Events (SSE) fluam de forma imediata sem serem retidos no buffer do Nginx.
