#!/bin/bash

# Esperar o MySQL estar pronto
echo "Aguardando MySQL..."
while ! php artisan db:monitor --timeout=1 > /dev/null 2>&1; do
    sleep 1
done
echo "MySQL está pronto!"

# Executar migrações
echo "Executando migrações..."
php artisan migrate --force

# Gerar chave da aplicação se não existir
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Configurar permissões
chmod -R 777 storage bootstrap/cache

# Iniciar o PHP-FPM
php-fpm 