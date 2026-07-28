# Backlog

Backlog vivo. Ao pegar um item pra trabalhar, siga o processo do `CLAUDE.md` (spec → plano aprovado → código → teste → atualizar este arquivo e o `CHANGELOG.md`). Ao achar um problema novo, adicione aqui em vez de deixar solto numa conversa.

Levantado na auditoria inicial de revitalização (2026-07-28). Nenhum destes itens foi corrigido ainda — só documentado.

## Escopo do MVP (fase 1 — agora)

Objetivo: site público bonito e funcional, um organizador, fluxo de inscrição + Pix correto e seguro, mesmo com poucos eventos e boa parte deles mocada via seeder.

- [ ] Frontend público reconstruído a partir do template em `TEMPLATES/Front-End/` (já recebido — falta escrever `docs/specs/frontend-publico.md` e planejar a adaptação)
- [ ] BUG-001 a BUG-005 corrigidos (todos os P0 abaixo — envolvem dinheiro e segurança de pagamento)
- [ ] BUG-006 corrigido (P1 — integridade multi-tenant básica)
- [ ] Deploy validado em produção com Postgres (esta rodada de trabalho)

## Fase 2 (depois do MVP no ar)

- [ ] Painel administrativo para o organizador (cadastrar evento, modalidade, kit, ver inscritos) usando o template SB Admin 2, que vai para `TEMPLATES/admin-sbadmin2/`
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

**BUG-002 — `modality_id`/`kit_id` sem integridade referencial**
`subscriptions.modality_id` e `subscriptions.kit_id` são colunas `string` soltas (migration `2026_03_01_234149_create_subscriptions_table.php`), sem foreign key — mesmo `EventModality`/`EventKit` sendo tabelas com PK numérica e o model `Subscription` declarar `belongsTo(EventModality::class)` / `belongsTo(EventKit::class)`. Hoje os relacionamentos só "funcionam" porque o ID numérico vira string por coincidência de tipo do PHP; não há garantia de que o valor gravado exista ou pertença ao evento certo.

**BUG-003 — Status `canceled` vs `cancelled` inconsistente**
A migration define o enum como `pending|paid|cancelled` (2 L's). `SubscribeController::subscribe()` compara com `'canceled'` (1 L) para decidir se reativa uma inscrição — uma comparação que nunca é verdadeira, porque `cancel()` **deleta** a linha em vez de mudar o status. Decidir um comportamento único (deletar ao cancelar, ou marcar como `cancelled`?) e corrigir a inconsistência.

**BUG-004 — Webhook do Mercado Pago sem validação de assinatura**
`MercadoPagoWebhookController::handle()` (`src/app/Http/Controllers/Subscriptions/MercadoPagoWebhookController.php`) aceita qualquer POST em `/api/webhooks/mercadopago` e marca a inscrição correspondente como paga, sem validar a assinatura/segredo que o Mercado Pago envia. Qualquer pessoa que descubra a URL e um `subscription_id` válido pode forjar a confirmação de pagamento.

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

**DEBT-004** — Cobertura de teste é zero, e o único teste que existe está **quebrado**: `tests/Feature/ExampleTest.php` faz `GET /`, que passa pelo `IdentifyOrganizerByDomain` (middleware global) e consulta a tabela `organizers` — mas `use RefreshDatabase` está comentado nesse teste, então as migrations nunca rodam no sqlite em memória e a query falha com `no such table: organizers`. Confirmado rodando `php artisan test` (`docs/runbook.md`) — pré-existente, não relacionado à troca pra Postgres. Prioridade: escrever testes de verdade junto com a correção de cada BUG-00X acima (o que inclui descomentar/consertar esse `RefreshDatabase`), não como projeto separado.

**DEBT-007** — Primeira subida do container `app` sem passar pelo `reset-dev.sh`/`reset-dev.bat` falha com HTTP 500: `storage/framework/{cache,sessions,views}` e `storage/logs` não existem no repo (diretórios vazios não vão pro git) e o container não os recria sozinho. Os scripts de reset já contornam isso manualmente (`mkdir -p` + `chmod`/`chown`); avaliar mover essa criação para um entrypoint do `docker/php/Dockerfile` pra não depender de rodar o script primeiro.

**DEBT-005** — `MercadoPagoService::createPixPayment` (`src/app/Services/MercadoPagoService.php`) chama `dd()` dentro do `catch` quando a API do Mercado Pago falha — isso interrompe a request com uma tela de debug, inclusive em produção. Deveria logar e devolver um erro tratável.

**DEBT-006** — Frontend inconsistente: Tailwind + Vite instalados mas o estilo real está em CSS solto por página (`src/public/css/*.css`). Vai ser endereçado pela reconstrução com o template (ver escopo do MVP).
