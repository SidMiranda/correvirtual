@echo off
cls

echo ==========================================
echo   RESET COMPLETO DO AMBIENTE LARAVEL
echo ==========================================
echo.

echo [1/7] Derrubando containers e volumes...
docker compose down -v

echo.
echo [2/7] Rebuildando imagens...
docker compose build --no-cache

echo.
echo [3/7] Subindo containers...
docker compose up -d

echo.
echo [4/7] Aguardando MySQL inicializar...
timeout /t 15 > nul

echo.
echo [5/7] Recriando estrutura do storage...

docker exec corre_app mkdir -p storage/framework/sessions
docker exec corre_app mkdir -p storage/framework/views
docker exec corre_app mkdir -p storage/framework/cache
docker exec corre_app mkdir -p storage/logs

docker exec corre_app touch storage/logs/laravel.log

echo.
echo [6/7] Ajustando permissoes...

docker exec corre_app chown -R www-data:www-data storage bootstrap/cache
docker exec corre_app chmod -R 775 storage bootstrap/cache

echo.
echo [7/7] Limpando cache e recriando banco...

docker exec corre_app php artisan optimize:clear
docker exec corre_app php artisan config:clear
docker exec corre_app php artisan cache:clear
docker exec corre_app php artisan route:clear
docker exec corre_app php artisan view:clear

docker exec corre_app php artisan migrate:fresh --force

echo.
echo ==========================================
echo   STATUS DOS CONTAINERS
echo ==========================================
docker ps

echo.
echo ==========================================
echo   AMBIENTE REINICIADO COM SUCESSO
echo ==========================================
pause