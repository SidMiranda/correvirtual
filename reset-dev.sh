#!/bin/bash

# Limpa a tela
clear

echo "=========================================="
echo "   RESET COMPLETO DO AMBIENTE (LINUX/WSL)"
echo "=========================================="
echo ""

# Overlay local troca o nginx para HTTP puro (produção exige certificado Let's Encrypt
# real e nunca sobe numa máquina de dev). Ver docker-compose.local.yml e docs/runbook.md.
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.local.yml"

echo "[1/7] Derrubando containers e volumes..."
$COMPOSE down -v

echo ""
echo "[2/7] Rebuildando imagens..."
$COMPOSE build --no-cache

echo ""
echo "[3/7] Subindo containers..."
$COMPOSE up -d

echo ""
echo "[4/7] Aguardando Postgres inicializar (pode demorar na primeira vez)..."
until docker exec corre_db pg_isready -U "${POSTGRES_USER:-corre_virtual}" -d "${POSTGRES_DB:-corre_virtual}" >/dev/null 2>&1; do
    echo "O banco de dados ainda não está pronto. Aguardando..."
    sleep 4
done
sleep 5 # Dá um tempinho extra de segurança após o serviço responder

echo ""
echo "[5/7] Ajustando permissões e pastas..."
# Garante que as pastas existam antes de dar permissão
docker exec -u root corre_app sh -c "mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/app/temp storage/logs bootstrap/cache"
docker exec -u root corre_app sh -c "touch storage/logs/laravel.log"
docker exec -u root corre_app sh -c "chmod -R 777 storage bootstrap/cache"
docker exec -u root corre_app sh -c "chown -R www-data:www-data storage bootstrap/cache"

echo ""
echo "[6/7] Instalando dependências e Gerando Keys..."
docker exec corre_app composer install --no-interaction --prefer-dist
docker exec corre_app php artisan key:generate
docker exec corre_app php artisan storage:link

echo ""
echo "[7/7] Limpando cache e recriando banco..."
docker exec corre_app php artisan optimize:clear
docker exec corre_app php artisan migrate:fresh --seed --force

echo ""
echo "=========================================="
echo "   STATUS DOS CONTAINERS"
echo "=========================================="
docker ps

echo ""
echo "AMBIENTE REINICIADO COM SUCESSO NO WSL"