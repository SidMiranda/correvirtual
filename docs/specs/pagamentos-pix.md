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
2. Extrai o ID do pagamento (`data.id` ou `id`, dependendo do formato da notificação).
3. Consulta a API do Mercado Pago pra confirmar o status real do pagamento (`MercadoPagoService::getPayment`) — **não confia cegamente no payload da notificação**, isso está correto.
4. Se `status === 'approved'`, usa `external_reference` (que é o `subscription_id` enviado na criação) pra marcar `Subscription.status = paid` e o `Payment` correspondente (por `transaction_id`) como `approved`.
5. Sempre responde `200` pro Mercado Pago parar de reenviar.

### Tela de sucesso
`GET /subscriptions/{id}/success` → `PixController::success`, puramente informativa.

## Bugs conhecidos nesta área

- **BUG-004 (webhook sem validação de assinatura):** o endpoint `/api/webhooks/mercadopago` não valida nenhum segredo/assinatura do Mercado Pago antes de consultar o pagamento e marcar como pago. Como o passo 3 acima *consulta a API real* antes de confirmar, o impacto prático é menor do que "aceitar qualquer POST cegamente" — mas ainda é possível forjar uma notificação apontando pra um `transaction_id`/pagamento real de outra pessoa se o atacante souber o ID. Validar a assinatura do webhook (o Mercado Pago fornece um jeito de fazer isso) fecha essa brecha.
- **BUG-001 (herdado):** o valor cobrado no Pix é o `Subscription.price`, que hoje é sempre `0.05` — corrigir isso é pré-requisito pra esta área funcionar corretamente com dinheiro real.
- **DEBT-005:** `MercadoPagoService::createPixPayment` usa `dd()` no `catch` de erro da API — derruba a request com uma tela de debug em vez de tratar o erro (ex.: devolver mensagem amigável e logar).
- `PaymentsController::pay()` é um stub vazio sem rota — código morto (DEBT-001).

## Fora de escopo hoje

- Outros meios de pagamento além de Pix (cartão, boleto) — só Pix está integrado.
- Reembolso/estorno — o status `refunded` existe na coluna mas nada no código o define.
- Expiração automática de cobrança Pix vencida (`expires_at` é gravado mas nada limpa/expira a inscrição `pending` associada).

## Plano de testes (a escrever ao corrigir os bugs desta área)

- `PixController@generatePix` cobra o valor do `EventKit`, não um valor fixo (depende de BUG-001 corrigido).
- Webhook rejeita notificação sem assinatura válida (depende de BUG-004 corrigido).
- Webhook marca `Subscription` e `Payment` corretos quando o pagamento consultado é `approved`.
- Webhook não quebra (responde 200 e loga) quando o `subscription_id`/`transaction_id` não existe.
- Falha da API do Mercado Pago ao criar o Pix não derruba a request com `dd()` (depende de DEBT-005 corrigido).
