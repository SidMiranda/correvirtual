# Eventos, modalidades, kits e inscrição

Status: Baseline (descreve o comportamento **atual**, bugs incluídos — ver `docs/backlog.md`)

## Problema

Um atleta precisa conseguir ver os eventos de um organizador, escolher uma modalidade (distância) e um kit, e se inscrever.

## Modelos envolvidos

- `Event` (`organizer_id`, `title`, `slug`, `description`, `location`, `event_date`, `registration_deadline`, `banner_url`, `active`)
- `EventModality` (`event_id`, `name`, `distance_km`, `max_participants`, `registered_count`, `active`)
- `EventKit` (`event_id`, `name`, `description`, `price`, `stock`, `sold`, `active`)
- `Subscription` (`event_id`, `user_id`, `modality_id`, `kit_id`, `price`, `bib_number`, `status`, `confirmed_at`) — único por `(event_id, user_id)` no banco

## Fluxos atuais

### Listar eventos do organizador atual
`GET /` → `EventsController@index`. Retorna eventos com `active = true` **e** `organizer_id` = organizador do domínio atual, ordenados por `event_date` ascendente, com `modalities` carregado.

### Ver detalhe de um evento
`GET /event/{event_id}` → `EventsController@show`. Busca por `organizer_id` do domínio atual + ID, com `modalities` e `kits` carregados. 404 se o evento não existir *ou não pertencer ao organizador atual* — este endpoint escopa por tenant corretamente.

### Formulário de inscrição
`GET /subscribe/event/{event_id}` (autenticado) → `SubscribeController::showSubscribeForm`. **Bug (BUG-005):** busca o evento só por ID (`Event::findOrFail`), sem filtrar por organizador do domínio atual — diferente do endpoint de detalhe acima.

### Criar inscrição
`POST /subscribe/event/{event_id}` (autenticado) → `SubscribeController::subscribe`.

Comportamento atual:
1. Busca o evento por ID (mesmo problema de escopo do item anterior — **BUG-005**, ainda aberto).
2. Valida que `modality_id` e `kit_id` vieram no request **e** que existem em `event_modalities`/`event_kits` pertencendo a este `event_id` (`Rule::exists(...)->where('event_id', ...)`) — corrigido em 2026-07-30 (BUG-002). As colunas `subscriptions.modality_id`/`kit_id` agora são `foreignId` de verdade, com `restrictOnDelete()` (não dá pra apagar um kit/modalidade que já tem inscrição).
3. Se já existe uma `Subscription` do usuário pra esse evento (só pode estar `pending` ou `paid` — ver "Cancelar inscrição" abaixo, cancelar apaga a linha) → redireciona pra "minhas inscrições" avisando que já está inscrito.
4. Se não existe, cria uma nova `Subscription` com `status = pending`, `price = 0.05` **fixo (BUG-001 — deveria ser o preço do `EventKit` escolhido, ainda aberto)**, `bib_number = null`.
5. Redireciona pra "minhas inscrições".

Não há verificação de `registration_deadline` nem de `max_participants` da modalidade (BUG-005, ainda aberto).

### Minhas inscrições
`GET /my-subscriptions` (autenticado) → `SubscribeController::mySubscriptions`. Lista inscrições do usuário logado, filtradas pelas que pertencem a eventos do organizador do domínio atual (`whereHas('event', ...)`) — este endpoint escopa por tenant corretamente.

### Cancelar inscrição
`POST /subscription/cancel` (autenticado) → `SubscribeController::cancel`. Só permite cancelar se `status === 'pending'`. Ao cancelar, **deleta a linha** (não muda pra `status = cancelled`) e apaga `Payment`s pendentes associados.

## Bugs conhecidos nesta área

Ver `docs/backlog.md`: BUG-001 (preço fixo, aberto), BUG-005 (sem escopo de tenant/prazo/capacidade no fluxo de inscrição, aberto). BUG-002 (FK ausente em modality_id/kit_id) e BUG-003 (`canceled` vs `cancelled`) corrigidos em 2026-07-30.

## Fora de escopo hoje

- Número de peito (`bib_number`) não é gerado em lugar nenhum ainda — coluna existe, nunca é preenchida.
- Não há painel para o organizador cadastrar evento/modalidade/kit — entra via seeder/banco direto (fase 2 do backlog).
- `registered_count` em `EventModality` existe na tabela mas não é incrementado por nenhum código atual.

## Plano de testes

Cobertos em `tests/Feature/SubscribeControllerTest.php`:
- `SubscribeController@subscribe` rejeita `modality_id`/`kit_id` que não pertencem ao `event_id`.
- `SubscribeController@subscribe` cria a inscrição com `modality_id`/`kit_id` válidos.
- `SubscribeController@subscribe` rejeita segunda inscrição ativa pro mesmo evento.
- Depois de cancelar (linha apagada), o usuário consegue se inscrever de novo sem erro de unique constraint.

Ainda por escrever (dependem de BUG-001/BUG-005 corrigidos):
- `EventsController@index` só retorna eventos do organizador do domínio atual.
- `SubscribeController@subscribe` grava `price` igual ao `EventKit.price` do kit escolhido, não um valor fixo.
- `SubscribeController@subscribe` rejeita inscrição após `registration_deadline`.
- `SubscribeController@subscribe` rejeita inscrição num evento de outro organizador.
