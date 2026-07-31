# Pagamento via Pix (Mercado Pago)

Status: Baseline (descreve o comportamento **atual**, bugs incluídos — ver `docs/backlog.md`)

## Problema

Depois de criar uma inscrição (`pending`), o atleta precisa pagar via Pix e ter a inscrição confirmada automaticamente quando o pagamento cair.

## Modelos envolvidos

- `Payment` (`subscription_id`, `provider` default `mercadopago`, `transaction_id`, `payment_method` default `pix`, `status` [`pending`,`approved`,`rejected`,`refunded`], `qr_code`, `qr_code_base64`, `ticket_url`, `expires_at`, `paid_at`, `payload` json)
- `App\Services\MercadoPagoService` — encapsula o SDK `mercadopago/dx-php`, autentica com `config('services.mercadopago.token')` (env `MERCADOPAGO_ACCESS_TOKEN`)

## Fluxo atual

### Gerar cobrança Pix
`POST /event-pay` → `PixController::generatePix`. Recebe `subscription_id`, busca a `Subscription`, chama `MercadoPagoService::createPixPayment($subscription->price, $user->email, $subscriptionId)` (o `price` aqui é o valor gravado na inscrição — ver BUG-001 em `eventos-e-inscricoes.md`, hoje sempre `0.05`). Cria um `Payment` com `status = pending` e os dados de QR code retornados. Renderiza `subscriptions.generate-pix` com o QR code.

### Cliente aguardando confirmação
A tela de QR code consulta `GET /api/subscriptions/{id}/status` (`PixController::checkStatus`) periodicamente (polling, a cada poucos segundos no frontend) só pra saber o `status` atual da `Subscription`.

### Confirmação via webhook
Mercado Pago notifica `POST /api/webhooks/mercadopago` → `MercadoPagoWebhookController::handle`:
1. Loga o payload recebido.
2. **Valida a assinatura** (`App\Services\MercadoPagoWebhookSignature::isValid`, corrigido em 2026-07-30 — BUG-004): lê os headers `x-signature`/`x-request-id` e o `data.id` da query string crua da notificação (via `Symfony\Component\HttpFoundation\HeaderUtils::parseQuery`, não `$request->query()` — PHP converte pontos em nome de query param pra underscore, então `data.id` viraria `data_id` se lido do jeito ingênuo), monta o manifest `id:{data.id};request-id:{x-request-id};ts:{ts};` e compara o HMAC-SHA256 (usando `MERCADOPAGO_WEBHOOK_SECRET`) com o `v1` do header. Se a assinatura for inválida ou o secret não estiver configurado, responde `401` e não processa nada — **falha fechada**.
3. Extrai o ID do pagamento (`data.id` ou `id`, dependendo do formato da notificação).
4. Consulta a API do Mercado Pago pra confirmar o status real do pagamento (`MercadoPagoService::getPayment`) — **não confia cegamente no payload da notificação**, isso está correto.
5. Se `status === 'approved'`, usa `external_reference` (que é o `subscription_id` enviado na criação) pra marcar `Subscription.status = paid` e o `Payment` correspondente (por `transaction_id`) como `approved`.
6. Sempre responde `200` pro Mercado Pago parar de reenviar (exceto quando a assinatura falha — nesse caso é `401`, de propósito, pra não confirmar recebimento de uma notificação não autenticada).

### Tela de sucesso
`GET /subscriptions/{id}/success` → `PixController::success`, puramente informativa.

## Bugs conhecidos nesta área

- **BUG-004 (corrigido em 2026-07-30):** o endpoint `/api/webhooks/mercadopago` não validava nenhum segredo/assinatura do Mercado Pago antes de consultar o pagamento e marcar como pago. Corrigido com validação de assinatura HMAC-SHA256 (ver "Confirmação via webhook" acima) — requer `MERCADOPAGO_WEBHOOK_SECRET` configurado, senão o webhook falha fechado.
- **BUG-001 (herdado, ainda aberto):** o valor cobrado no Pix é o `Subscription.price`, que hoje é sempre `0.05` — corrigir isso é pré-requisito pra esta área funcionar corretamente com dinheiro real.
- **DEBT-005:** `MercadoPagoService::createPixPayment` usa `dd()` no `catch` de erro da API — derruba a request com uma tela de debug em vez de tratar o erro (ex.: devolver mensagem amigável e logar).
- `PaymentsController::pay()` é um stub vazio sem rota — código morto (DEBT-001).

## Fora de escopo hoje

- Outros meios de pagamento além de Pix (cartão, boleto) — só Pix está integrado.
- Reembolso/estorno — o status `refunded` existe na coluna mas nada no código o define.
- Expiração automática de cobrança Pix vencida (`expires_at` é gravado mas nada limpa/expira a inscrição `pending` associada).

## Plano de testes

Cobertos:
- `App\Services\MercadoPagoWebhookSignature::isValid` — assinatura válida aceita, `v1`/`data.id`/secret errados ou ausentes rejeitados (`tests/Unit/MercadoPagoWebhookSignatureTest.php`).
- Webhook rejeita notificação sem assinatura válida ou sem secret configurado, sem alterar a `Subscription` (`tests/Feature/MercadoPagoWebhookControllerTest.php`).

Ainda por escrever:
- `PixController@generatePix` cobra o valor do `EventKit`, não um valor fixo (depende de BUG-001 corrigido).
- Webhook marca `Subscription` e `Payment` corretos quando o pagamento consultado é `approved` — hoje não dá pra testar sem bater na API real do Mercado Pago (`MercadoPagoService` não é mockável, ver DEBT-008 em `docs/backlog.md`).
- Webhook não quebra (responde 200 e loga) quando o `subscription_id`/`transaction_id` não existe.
- Falha da API do Mercado Pago ao criar o Pix não derruba a request com `dd()` (depende de DEBT-005 corrigido).
