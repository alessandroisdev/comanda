#!/bin/sh
# wait-for-mysql.sh

host="$1"
shift
cmd="$@"

echo "Aguardando o banco MySQL estar pronto para conexões em ${host}..."

# Testar conexão real via PHP PDO com credenciais
until php -r "
try {
    \$host = '${host}';
    \$user = getenv('DB_USERNAME') ?: 'root';
    \$pass = getenv('DB_PASSWORD') ?: 'root';
    new PDO(\"mysql:host=\$host\", \$user, \$pass);
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
  >&2 echo "MySQL em ${host} ainda está indisponível - aguardando..."
  sleep 2
done

>&2 echo "MySQL em ${host} está ativo e aceitando conexões - continuando!"
exec $cmd
