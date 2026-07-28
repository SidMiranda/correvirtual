# Visão geral do produto

## O que é

O Corre Virtual é uma plataforma de inscrições para eventos de corrida — corridas de rua presenciais e desafios virtuais (onde o participante cobre uma distância no período do evento, sem largada única). Cada organizador de eventos tem sua própria "loja" na plataforma, acessível pelo domínio configurado para ele (multi-tenant). Um atleta se cadastra, escolhe um evento, escolhe uma modalidade (distância) e um kit (o que ele recebe/paga), e paga a inscrição via Pix.

## Por que existe

Pequenos organizadores de corrida (grupos de treino, assessorias esportivas, coletivos) não têm orçamento para as plataformas grandes do mercado. O Corre Virtual nasceu para atender esse nicho, começando pelo próprio organizador do dono do projeto.

## Glossário

| Termo | Significado no sistema |
|---|---|
| **Organizer** (organizador) | O tenant. Quem cria e vende eventos. Identificado pelo domínio da requisição. Ex.: "Corre Virtual Eventos", "Borafitness Eventos". |
| **Event** (evento) | Uma corrida ou desafio, pertence a um organizador. Tem data, local, prazo de inscrição, descrição, banner. |
| **EventModality** (modalidade) | Uma distância/categoria dentro do evento. Ex.: 5km, 10km, Caminhada 3km, ou "30/50/70/100km" num desafio virtual. |
| **EventKit** (kit) | O que o atleta recebe/paga ao se inscrever numa modalidade — ex. "Kit Camiseta" (R$ 79,90), "Kit Digital" (sem brinde físico). Tem preço próprio. |
| **User** (atleta) | Pessoa que se cadastra e se inscreve em eventos. Tem CPF, e-mail verificado por código, papel (`athlete`, `organizer_admin`, `super_admin` — só `athlete` está implementado hoje). |
| **Subscription** (inscrição) | O vínculo entre um `User` e um `Event`, com a modalidade e o kit escolhidos, preço cobrado, status (`pending`/`paid`/`cancelled`) e número de peito (`bib_number`, gerado após pagamento — geração ainda não implementada). |
| **Payment** (pagamento) | Um pagamento Pix gerado via Mercado Pago para uma inscrição — QR code, status, timestamps. Uma inscrição pode ter mais de uma tentativa de pagamento. |

## Fluxo principal (o que já funciona hoje)

1. Visitante acessa o domínio do organizador → vê a lista de eventos ativos dele (`EventsController@index`).
2. Abre um evento → vê modalidades e kits (`EventsController@show`).
3. Cria conta (CPF + e-mail + senha) → confirma e-mail por código de 4 dígitos.
4. Faz login (por e-mail ou CPF) → escolhe modalidade + kit → gera inscrição (`pending`).
5. Gera cobrança Pix para a inscrição → paga → webhook do Mercado Pago confirma e marca a inscrição como `paid`.
6. Acompanha suas inscrições em "Minhas inscrições", pode cancelar enquanto `pending`.

Esse fluxo tem bugs conhecidos que afetam principalmente o passo 4→5 (valor cobrado) e 5 (segurança do webhook) — ver [`backlog.md`](backlog.md) e [`specs/pagamentos-pix.md`](specs/pagamentos-pix.md).

## Estágio atual (MVP)

- Um organizador ativo de fato, poucos eventos.
- **Está tudo bem os eventos serem mocados via seeder** — o objetivo agora é ter a plataforma no ar, bonita e correta no fluxo de dinheiro, não um catálogo real completo.
- Painel administrativo para o organizador cadastrar eventos **não existe ainda** — hoje eventos só entram via seeder/banco direto. É a fase 2 (ver backlog).
- Frontend público será refeito a partir de um template de referência (`TEMPLATES/Front-End/`, já recebido).

## Personas

- **Atleta:** quer achar um evento, se inscrever rápido, pagar por Pix, e no dia ter seu número de peito/kit certos. Prioriza confiança (é comum desconfiar de plataformas pequenas cobrando dinheiro).
- **Organizador (fase 2):** quer cadastrar evento, modalidades e kits sem precisar pedir pro desenvolvedor, e ver quem se inscreveu.
- **Dono da plataforma (super_admin, não implementado):** opera múltiplos organizadores.
