#!/bin/bash

# Limpa a tela
clear

echo "=========================================="
echo "   RESET COMPLETO DO AMBIENTE (LINUX/WSL)"
echo "=========================================="
echo ""

# Overlay local troca o nginx para HTTP puro (produção exige certificado Let's Encrypt
# real e nunca sobe numa máquina de dev). Ver docker-compose.local.yml e docs/runbook.md.
# Banco (dev e prod) fica na Hostgator, não em container local — ver docs/decisoes/0005.
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.local.yml"

echo "[1/6] Derrubando containers e volumes..."
$COMPOSE down -v

echo ""
echo "[2/6] Rebuildando imagens..."
$COMPOSE build --no-cache

echo ""
echo "[3/6] Subindo containers..."
$COMPOSE up -d

echo ""
echo "[4/6] Ajustando permissões e pastas..."
# Garante que as pastas existam antes de dar permissão
docker exec -u root corre_app sh -c "mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/app/temp storage/logs bootstrap/cache"
docker exec -u root corre_app sh -c "touch storage/logs/laravel.log"
docker exec -u root corre_app sh -c "chmod -R 777 storage bootstrap/cache"
docker exec -u root corre_app sh -c "chown -R www-data:www-data storage bootstrap/cache"

echo ""
echo "[5/6] Instalando dependências e Gerando Keys..."
docker exec corre_app composer install --no-interaction --prefer-dist
docker exec corre_app php artisan key:generate
docker exec corre_app php artisan storage:link

echo ""
echo "[6/6] Limpando cache e recriando banco remoto (Hostgator dev)..."
docker exec corre_app php artisan optimize:clear
docker exec corre_app php artisan migrate:fresh --seed --force

echo ""
echo "=========================================="
echo "   STATUS DOS CONTAINERS"
echo "=========================================="
docker ps

echo ""
echo "AMBIENTE REINICIADO COM SUCESSO NO WSL"