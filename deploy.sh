#!/bin/bash
# Navega até a pasta do projeto
cd /home/correvirtual

echo "🚀 Iniciando Deploy..."

# Puxa a versão mais nova do GitHub
git pull origin main

# Sobe os containers (o --build reconstrói se houver mudança no Dockerfile)
docker-compose up -d --build

# Comandos internos do Laravel
docker exec seu_container_php php artisan migrate --force
docker exec seu_container_php php artisan optimize

echo "✅ Deploy finalizado com sucesso!"