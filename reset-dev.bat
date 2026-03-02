@echo off
cls

echo ==========================================
echo   RESET COMPLETO DO AMBIENTE LARAVEL
echo ==========================================
echo.

echo [1/6] Derrubando containers e limpando o banco de dados...
docker compose down -v

echo.
echo [2/6] Buildando e subindo imagens...
docker compose up -d --build

echo.
echo [3/6] Aguardando o MySQL iniciar (um banco zerado demora uns segundos)...
timeout /t 15 > nul

echo.
echo [4/6] Recriando estrutura do storage (Volume Limpo)...
docker exec -it corre_app mkdir -p storage/framework/sessions
docker exec -it corre_app mkdir -p storage/framework/views
docker exec -it corre_app mkdir -p storage/framework/cache
docker exec -it corre_app mkdir -p storage/logs

echo.
echo [5/6] Ajustando permissoes e donos...
docker exec -it corre_app chown -R www-data:www-data storage bootstrap/cache
docker exec -it corre_app chmod -R 775 storage bootstrap/cache

echo.
echo [6/6] Limpando caches e rodando migrations do zero...
docker exec -it corre_app php artisan optimize:clear
docker exec -it corre_app php artisan migrate:fresh --force

echo.
echo Status final dos containers:
docker ps

echo.
echo ==========================================
echo   AMBIENTE REINICIADO COM SUCESSO
echo ==========================================
pause