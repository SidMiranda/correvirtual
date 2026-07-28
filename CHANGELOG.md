# Changelog

Todas as mudanças relevantes do projeto são registradas aqui. Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/).

Histórico anterior a este arquivo (todo o desenvolvimento inicial do projeto) pode ser consultado via `git log` — não foi reconstruído retroativamente aqui.

## [Unreleased]

### Added
- Documentação viva do projeto: `docs/visao-geral.md`, `docs/arquitetura.md`, `docs/runbook.md`, `docs/backlog.md`, ADRs em `docs/decisoes/` e specs baseline em `docs/specs/`.
- `CLAUDE.md` com as regras de trabalho (SDD, plano antes de código, testes obrigatórios).
- Serviço Postgres 16 no `docker-compose.yml` (o banco anterior, hospedado em outro provedor, foi perdido).
- Pasta `TEMPLATES/` documentada (`TEMPLATES/README.md`) — já recebeu o template do site público em `TEMPLATES/Front-End/`.
- `docker/nginx/local.conf` + `docker-compose.local.yml`: overlay opcional só para dev local (HTTP puro, sem depender dos certificados Let's Encrypt de produção). Produção continua usando `docker-compose.yml` sozinho, como já era.

### Changed
- `docker/php/Dockerfile` passou a instalar `pdo_pgsql` em vez de `pdo_mysql`.
- `src/.env.example` atualizado com as variáveis do Postgres e `MERCADOPAGO_ACCESS_TOKEN` (antes ausente).
- `.github/workflows/deploy.yml` agora sincroniza automaticamente as credenciais do Postgres a partir do `.env` do Laravel a cada deploy.
- `reset-dev.sh` passou a aguardar o healthcheck do Postgres (`corre_db`) em vez do MySQL.

### Verificado nesta rodada
Stack local validada de ponta a ponta com os containers reais (`docker compose up -d --build`): migrations rodam limpas em Postgres 16, seeders populam 2 organizadores / 6 eventos / 17 modalidades / 9 kits, e a home carrega os eventos via nginx (`http://localhost`, HTTP 200). Detalhes e comandos em `docs/runbook.md`.

### Known issues
Ver `docs/backlog.md` para a lista completa levantada na auditoria inicial (preço de inscrição fixo incorreto, webhook do Mercado Pago sem validação de assinatura, `organizer_id` não preenchido no cadastro, entre outros). Nenhum desses bugs foi corrigido nesta rodada — só documentado. Além disso, `php artisan test` está **quebrado** hoje por um motivo pré-existente e não relacionado ao Postgres (`DEBT-004` em `docs/backlog.md`).
