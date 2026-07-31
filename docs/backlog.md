# Backlog

Backlog vivo. Ao pegar um item pra trabalhar, siga o processo do `CLAUDE.md` (spec → plano aprovado → código → teste → atualizar este arquivo e o `CHANGELOG.md`). Ao achar um problema novo, adicione aqui em vez de deixar solto numa conversa.

Levantado na auditoria inicial de revitalização (2026-07-28). Nenhum destes itens foi corrigido ainda — só documentado.

## Escopo do MVP (fase 1 — agora)

Objetivo: site público bonito e funcional, um organizador, fluxo de inscrição + Pix correto e seguro, mesmo com poucos eventos e boa parte deles mocada via seeder.

- [x] Home v2 (nav de duas camadas + banner rotativo, cores do template recolorido em azul) — ver `docs/specs/frontend-publico.md`. Implementado em `feature/home-v2-design`, **aguardando revisão visual do organizador** antes de: (a) mergear, (b) gerar as imagens do banner via Gemini (falta `GEMINI_API_KEY`), (c) replicar pras outras páginas.
- [ ] Replicar o redesign da Home v2 pras outras páginas públicas (login, detalhe de evento, inscrição) — só depois do organizador aprovar a v2
- [ ] BUG-001 e BUG-005 corrigidos (P0 restantes — envolvem dinheiro e segurança de pagamento). BUG-002, BUG-003 e BUG-004 já corrigidos (2026-07-30).
- [ ] BUG-006 corrigido (P1 — integridade multi-tenant básica)
- [ ] Deploy validado em produção com Postgres (esta rodada de trabalho)

## Antes do deploy de produção real

- [ ] **Decidir onde mora o banco de produção de verdade** — Postgres em container no VPS (ADR 0001/0004, o que está implementado) ou algo gerenciado na Hostgator (mencionado como possível, não confirmado — "se não me engano"). Se for Hostgator, provavelmente é MySQL/MariaDB, não Postgres — registrar numa ADR nova antes de mudar. A conexão já é 100% via `.env` dos dois lados (app e docker-compose), então o código não muda; só a configuração. Ver nota em `docs/decisoes/0004-deploy-vps-docker-git-flow.md`.
- [ ] Atualizar o secret `APP_ENV` no GitHub com credenciais do banco de produção real (qualquer que seja o destino acima).

## Fase 2 (depois do MVP no ar)

- [ ] Painel administrativo para o organizador (cadastrar evento, modalidade, kit, ver inscritos) usando o template SB Admin Pro, já recebido em `TEMPLATES/Painel-Admin/` (inclui telas prontas de seleção/criação de tenant)
- [ ] Geração de número de peito (`bib_number`) após pagamento confirmado
- [ ] BUG-007 (throttle em login/registro/verificação)
- [ ] Papéis `organizer_admin` / `super_admin` (hoje só `athlete` é usado de fato)

## Fase 3 / ideias futuras (não priorizado)

- Check-in / retirada de kit no dia do evento
- Lotes de preço por data (preço sobe conforme se aproxima do evento)
- E-mail transacional de confirmação de inscrição/pagamento (hoje só existe e-mail de verificação de cadastro)
- Relatório/exportação de inscritos para o organizador

---

## Bugs e débito técnico

### P0 — dinheiro e segurança de pagamento

**BUG-001 — Preço da inscrição hardcoded em R$ 0,05**
`SubscribeController::subscribe()` (`src/app/Http/Controllers/Subscriptions/SubscribeController.php`) grava `'price' => 0.05` tanto ao criar quanto ao reativar uma inscrição, ignorando o preço do `EventKit` escolhido. Esse é o valor que a `PixController` depois cobra de verdade via Mercado Pago. Qualquer inscrição paga hoje cobraria 5 centavos, não o preço do kit.
*Spec relacionado:* `specs/eventos-e-inscricoes.md`, `specs/pagamentos-pix.md`.

**BUG-002 — ~~`modality_id`/`kit_id` sem integridade referencial~~ (corrigido em 2026-07-30)**
`subscriptions.modality_id` e `subscriptions.kit_id` eram colunas `string` soltas, sem foreign key — mesmo `EventModality`/`EventKit` sendo tabelas com PK numérica e o model `Subscription` declarar `belongsTo(EventModality::class)` / `belongsTo(EventKit::class)`. Corrigido pela migration `2026_07_30_000000_fix_subscriptions_modality_kit_foreign_keys.php` (colunas agora são `foreignId` com `restrictOnDelete()`) + validação em `SubscribeController::subscribe()` (`Rule::exists` checando que a modalidade/kit pertence ao `event_id`). Teste: `tests/Feature/SubscribeControllerTest.php`.

**BUG-003 — ~~Status `canceled` vs `cancelled` inconsistente~~ (corrigido em 2026-07-30)**
A migration define o enum como `pending|paid|cancelled` (2 L's), mas `SubscribeController::subscribe()` comparava com `'canceled'` (1 L) — comparação sempre verdadeira, e o branch de reativação nunca era executado. Decisão: manter o comportamento já implementado em `cancel()` (deleta a linha em vez de marcar `cancelled`); removido o branch morto de reativação. Teste: `tests/Feature/SubscribeControllerTest.php`.

**BUG-004 — ~~Webhook do Mercado Pago sem validação de assinatura~~ (corrigido em 2026-07-30)**
`MercadoPagoWebhookController::handle()` aceitava qualquer POST em `/api/webhooks/mercadopago` sem validar a assinatura/segredo que o Mercado Pago envia. Corrigido com `App\Services\MercadoPagoWebhookSignature` (valida o header `x-signature` via HMAC-SHA256, conforme algoritmo documentado pelo Mercado Pago) — requer `MERCADOPAGO_WEBHOOK_SECRET` configurado (`src/config/services.php` / `.env`); sem o secret, o webhook falha fechado (401). Testes: `tests/Unit/MercadoPagoWebhookSignatureTest.php`, `tests/Feature/MercadoPagoWebhookControllerTest.php`.

**BUG-005 — Sem validação de prazo, capacidade ou tenant na inscrição**
`SubscribeController::showSubscribeForm`/`subscribe` buscam o `Event` só pelo ID (`Event::findOrFail($eventId)`), sem checar: (a) se o evento pertence ao organizador do domínio atual, (b) se `registration_deadline` já passou, (c) se a modalidade atingiu `max_participants`. Um usuário logado no domínio do organizador A consegue se inscrever num evento do organizador B só sabendo o ID.

### P1 — autenticação / multi-tenant

**BUG-006 — `organizer_id` do usuário nunca é preenchido**
`RegisterController::register()` (`src/app/Http/Controllers/Auth/RegisterController.php`) não seta `organizer_id` ao criar o `User`, mesmo a coluna existindo e `app('currentOrganizer')` estando disponível no momento do cadastro. Usuários não ficam de fato vinculados ao tenant onde se cadastraram.

**BUG-007 — Sem throttle em login/registro/verificação de e-mail**
`/login`, `/register`, `/verify-email` não têm rate limiting. Baixo risco enquanto o site tem pouco tráfego, mas precisa entrar antes de ganhar tráfego real.

### P2 — código morto (candidatos a remoção — confirmar antes de apagar)

**DEBT-001** — `PaymentsController::pay()` (`src/app/Http/Controllers/Subscriptions/PaymentsController.php`) é um stub vazio, nenhuma rota aponta pra ele.

**DEBT-002** — `SubscribeController_old.php`, `Subscription_old.php`, `routes/web_old.php` não têm nenhuma referência ativa no projeto, mas foram tocados em commits recentes (`dba40cb`) — **confirmar com o dono do projeto** antes de apagar, pode ser material de referência da migração ainda em uso mental.

**DEBT-003** — `resources/views/components/my-subscriptions.blade.php` (fora de `components/app/`) e `components/app/old-top-bar.blade.php` são duplicatas órfãs. As views realmente usadas são `components/app/my-subscriptions.blade.php` e `components/app/top-bar.blade.php` (confirmado via grep nas views que usam `<x-app.*>`).

### P3 — infraestrutura / qualidade

**DEBT-004** — ~~Cobertura de teste é zero, e o único teste que existe está quebrado~~ (parcialmente corrigido em 2026-07-30). `RefreshDatabase` foi religado em `tests/Feature/ExampleTest.php` (precisava de um `Organizer` com `domain = 'localhost'`, já que `IdentifyOrganizerByDomain` roda em toda request e o host default de teste é `localhost`) e testes reais foram escritos junto da correção de BUG-002/003/004. Ainda falta cobertura para BUG-001 e BUG-005 quando forem corrigidos, e para os fluxos de auth/Pix em geral.

**DEBT-008** — `MercadoPagoService::getPayment`/`createPixPayment` são métodos estáticos que chamam o SDK do Mercado Pago direto, sem nenhum seam pra mock. Isso impede testar o caminho feliz do webhook (assinatura válida + pagamento aprovado) sem bater na API real — hoje só o caminho de rejeição (assinatura inválida) tem teste automatizado (`tests/Feature/MercadoPagoWebhookControllerTest.php`). Resolver exigiria transformar `MercadoPagoService` em algo injetável (classe com métodos de instância + binding no container, ou uma interface).

**DEBT-007** — Primeira subida do container `app` sem passar pelo `reset-dev.sh`/`reset-dev.bat` falha com HTTP 500: `storage/framework/{cache,sessions,views}` e `storage/logs` não existem no repo (diretórios vazios não vão pro git) e o container não os recria sozinho. Os scripts de reset já contornam isso manualmente (`mkdir -p` + `chmod`/`chown`); avaliar mover essa criação para um entrypoint do `docker/php/Dockerfile` pra não depender de rodar o script primeiro.

**DEBT-005** — `MercadoPagoService::createPixPayment` (`src/app/Services/MercadoPagoService.php`) chama `dd()` dentro do `catch` quando a API do Mercado Pago falha — isso interrompe a request com uma tela de debug, inclusive em produção. Deveria logar e devolver um erro tratável.

**DEBT-006** — Frontend inconsistente: Tailwind + Vite instalados mas o estilo real está em CSS solto por página (`src/public/css/*.css`). Endereçado parcialmente pela Home v2 (ver escopo do MVP); as outras páginas continuam nesse padrão até o redesign ser replicado.

**DEBT-009** — `head.blade.php` monta o caminho do favicon como `'images/organizers/'.$organizerId.'logo.png'` — falta uma barra entre o ID e `logo.png` (vira `images/organizers/1logo.png`, sempre 404). Achado navegando a Home v2 (console do navegador). Pré-existente em todas as páginas, não relacionado à Home v2 — não corrigido nesta rodada por estar fora do escopo (só design da Home).
