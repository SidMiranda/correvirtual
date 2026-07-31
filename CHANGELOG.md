# Changelog

Todas as mudanças relevantes do projeto são registradas aqui. Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/).

Histórico anterior a este arquivo (todo o desenvolvimento inicial do projeto) pode ser consultado via `git log` — não foi reconstruído retroativamente aqui.

## [Unreleased]

### Fixed
- **BUG-002**: `subscriptions.modality_id`/`kit_id` agora são foreign keys de verdade (`event_modalities`/`event_kits`, com `restrictOnDelete()`), e `SubscribeController::subscribe()` valida que a modalidade/kit escolhido pertence ao evento antes de criar a inscrição.
- **BUG-003**: removida a comparação `status !== 'canceled'` (nunca era verdadeira) e o branch morto de "reativar inscrição cancelada" em `SubscribeController::subscribe()` — cancelar continua apagando a linha (`SubscribeController::cancel()`), então uma inscrição encontrada só pode estar `pending` ou `paid`.
- **BUG-004**: `POST /api/webhooks/mercadopago` agora valida a assinatura HMAC-SHA256 do Mercado Pago (`App\Services\MercadoPagoWebhookSignature`) antes de processar qualquer notificação; rejeita com `401` se a assinatura for inválida ou `MERCADOPAGO_WEBHOOK_SECRET` não estiver configurado.
- `tests/Feature/ExampleTest.php`: religado `RefreshDatabase` (estava comentado, migrations nunca rodavam no sqlite em memória).

### Added
- Testes automatizados para os três fixes acima: `tests/Feature/SubscribeControllerTest.php`, `tests/Unit/MercadoPagoWebhookSignatureTest.php`, `tests/Feature/MercadoPagoWebhookControllerTest.php`.
- `MERCADOPAGO_WEBHOOK_SECRET` em `src/config/services.php` e `src/.env.example`.
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
Ver `docs/backlog.md` para a lista completa levantada na auditoria inicial. BUG-002, BUG-003 e BUG-004 foram corrigidos nesta rodada (2026-07-30); seguem abertos: BUG-001 (preço de inscrição fixo incorreto), BUG-005 (sem validação de prazo/capacidade/tenant na inscrição), BUG-006 (`organizer_id` não preenchido no cadastro), entre outros.
