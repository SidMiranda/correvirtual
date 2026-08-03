# Changelog

Todas as mudanças relevantes do projeto são registradas aqui. Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/).

Histórico anterior a este arquivo (todo o desenvolvimento inicial do projeto) pode ser consultado via `git log` — não foi reconstruído retroativamente aqui.

## [Unreleased]

### Fase de teste em produção (2026-08-02/03)
- **Preço de teste temporário (R$0,05)**: `PixAmountResolver` sobrepõe o valor cobrado no Pix pra qualquer evento/kit enquanto `MERCADOPAGO_TEST_PRICE_ENABLED=true` — decisão do Sidney pra encher a plataforma de testes sem cobrar valor cheio. Não mexe em `Subscription::price` (continua o preço real do kit). Reverter é só trocar a env var pra `false`. Ver `docs/backlog.md`.
- **Credencial Mercado Pago trocada pra conta do Uéslei** (era a do Sidney) — a antiga ficou comentada em `src/.env`, não apagada.
- **DEBT-005 corrigido**: `MercadoPagoService::createPixPayment` não faz mais `dd()` quando a API do Mercado Pago falha — loga o erro e devolve `null`; `PixController::generatePix` mostra "Estamos com instabilidade no pagamento no momento. Tente novamente mais tarde." em vez de derrubar a request com uma tela de debug. Achado ao vivo testando com a credencial nova (ver BUG-008 no backlog — a conta do Uéslei ainda não está liberada pro Mercado Pago processar pagamentos live).

### Infraestrutura de produção (2026-08-02) — site no ar
- **`https://eventos.correvirtual.com.br` está em produção.** VPS (Hostgator, `143.95.218.62`) provisionada do zero: Docker + Docker Compose instalados, repositório clonado, `.env` de produção configurado (banco, Mercado Pago real, `APP_DEBUG=false`, `APP_KEY` novo), certificado TLS real emitido via certbot (Let's Encrypt, renovação automática agendada, expira 2026-10-31).
- **Decisão**: banco de produção e desenvolvimento migram pra MySQL gerenciado na Hostgator (nada de banco local) — ver `docs/decisoes/0005-banco-producao-hostgator-mysql.md`. Migrations + seed rodados com sucesso em `webcit29_eventos_prod` e `webcit29_eventos_dev`.
- `docker/php/Dockerfile`: adiciona `pdo_mysql` (mantém `pdo_pgsql` por enquanto).
- Secrets do GitHub Actions (`HOST`, `PORT`, `USERNAME`, `PASSWORD`, `APP_ENV`) atualizados — o deploy automático (`.github/workflows/deploy.yml`, dispara em push pra `main`) está funcional pra próximas atualizações.
- Fluxo completo validado via Playwright contra o ambiente real (banco remoto): cadastro → verificação de e-mail → login automático → escolha de evento/modalidade/kit → inscrição criada com o preço correto do kit (R$59,90, não o antigo valor fixo). Não testado o passo de gerar o Pix em si, de propósito — evitar chamar a API real do Mercado Pago numa sessão de teste.
- Único pendente conhecido: `MERCADOPAGO_WEBHOOK_SECRET` de produção ainda não configurado — ver "Known issues".

### Home v2
- Redesign da Home (`/`): menu de duas camadas (barra utilitária + navegação principal, sticky) e banner rotativo com CTAs, inspirados em `TEMPLATES/Front-End/` e recoloridos pra azul escuro/claro (`--cv-navy` `#0d1b2a` + `--cv-blue` `#1a71b2`, já usados no projeto). Detalhes em `docs/specs/frontend-publico.md`.
- Novos arquivos: `layouts/app-v2.blade.php`, `components/app/nav-v2.blade.php`, `components/app/banner-v2.blade.php`, `public/css/home-v2.css`, `public/js/home-v2.js` (vanilla, sem jQuery/Bootstrap/Swiper novos). Só `index.blade.php` usa o layout novo — todas as outras páginas continuam em `layouts/app.blade.php`, intocado.
- `php artisan images:generate-gemini`: gera as imagens do banner via Gemini (offline, uma vez só — nunca em runtime). Executado com sucesso — `public/images/home-v2/banner-{1,2,3}.jpg` gerados e já usados nos slides 2 e 3 do banner (slide 1 continua priorizando o banner real do organizador quando existe).
- Ajuste após 1ª revisão visual do organizador: nome do organizador e botões de autenticação estavam duplicados nas duas barras do menu — barra utilitária virou só tagline; toda a autenticação (Entrar/Criar conta/Minhas inscrições/Sair) passou a viver só na barra principal, que trocou o fundo branco por um tom azul claro (`--cv-blue-pale`) pra combinar com o resto da paleta.
- Ajuste após 2ª revisão: banner-1-organizer-cropped.jpg — recorte do banner real do organizador removendo o bloco de logo, mantendo a ponte de Mogi Guaçu, que passou a ser o slide 1 do banner (no lugar da imagem crua com a logo). Ícones de rede social na barra utilitária removidos (usuário reportava desalinhamento não reproduzível mesmo após limpar cache; não foi encontrada a causa — removidos por segurança em vez de continuar investigando). `layouts/app-v2.blade.php` ganhou cache-busting (`?v={{ filemtime(...) }}`) no CSS/JS da v2, pra evitar esse tipo de divergência "funciona aqui, não funciona aí" de novo.
- Corrigido de passagem (achado testando a v2, afeta o site todo): `.block-header-title` sem `flex-wrap` estourava a largura da tela em mobile; `--navy` era usada em `global.css` mas nunca definida.

### Fixed
- **BUG-001**: `SubscribeController::subscribe()` gravava `price => 0.05` fixo em toda inscrição, ignorando o preço do `EventKit` escolhido — agora usa `$kit->price`. Teste: `tests/Feature/SubscribeControllerTest.php`.
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
Ver `docs/backlog.md` para a lista completa. BUG-001 a BUG-004 corrigidos; seguem abertos BUG-005 (sem validação de prazo/capacidade/tenant na inscrição) e BUG-006 (`organizer_id` não preenchido no cadastro). Do primeiro deploy de produção (2026-08-02): `MERCADOPAGO_WEBHOOK_SECRET` ainda não configurado (webhook do Pix rejeita tudo até isso ser feito — único bloqueador pra pagamento funcionar de ponta a ponta); rotina de backup do banco de produção ainda não definida.
