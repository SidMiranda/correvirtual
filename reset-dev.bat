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
echo [5/7] Instalando dependencias e ajustando permissoes (Root)...

REM Cria a estrutura de pastas forcadamente caso nao exista
docker exec -u root corre_app sh -c "mkdir -p storage/framework/sessions"
docker exec -u root corre_app sh -c "mkdir -p storage/framework/views"
docker exec -u root corre_app sh -c "mkdir -p storage/framework/cache"
docker exec -u root corre_app sh -c "mkdir -p storage/app/temp"
docker exec -u root corre_app sh -c "mkdir -p storage/logs"
docker exec -u root corre_app sh -c "touch storage/logs/laravel.log"

REM Aplica permissoes 777 para evitar qualquer erro de escrita no Windows/Docker
docker exec -u root corre_app sh -c "chmod -R 777 storage bootstrap/cache storage/app/temp"
docker exec -u root corre_app sh -c "chown -R www-data:www-data storage bootstrap/cache storage/app/temp"

REM Garante que os arquivos gerados pelo Artisan (root no container) sejam editaveis pelo seu usuario no VS Code (WSL UID 1000)
docker exec -u root corre_app sh -c "chown -R 1000:1000 /var/www/app /var/www/database /var/www/routes /var/www/config"
docker exec -u root corre_app sh -c "chmod -R 775 /var/www/app /var/www/database /var/www/routes /var/www/config"

REM Garante que as dependencias do PHP estao instaladas (Agora com a pasta temp existindo)
docker exec corre_app composer install --no-interaction --prefer-dist

REM Gera a chave de criptografia do Laravel se nao existir
docker exec corre_app php artisan key:generate

REM Cria o link simbolico para imagens publicas (essencial para os banners)
docker exec corre_app php artisan storage:link

echo.
echo [7/7] Limpando cache e recriando banco...

docker exec corre_app php artisan optimize:clear
docker exec corre_app php artisan config:clear
docker exec corre_app php artisan cache:clear
docker exec corre_app php artisan route:clear
docker exec corre_app php artisan view:clear

REM Roda as migrations e os SEEDERS (Cria os eventos CarnaRun, etc)
docker exec corre_app php artisan migrate:fresh --seed --force

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

@REM docker exec -u 1000:1000 corre_app php artisan make:controller MeuController
