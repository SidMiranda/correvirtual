# 0002 — Multi-tenancy por domínio

Status: Aceita

## Contexto

O projeto já nasceu multi-tenant: uma tabela `organizers` e um middleware (`IdentifyOrganizerByDomain`) que resolve o tenant atual pelo host da requisição HTTP, guardando o resultado em `app('currentOrganizer')` e compartilhando com as views. Isso já está implementado e funcionando para listagem/detalhe de evento. Ao revitalizar o projeto, a pergunta era: manter esse modelo ou trocar por outro (subdomínio, path prefix, tenant selecionado por login)?

## Decisão

Manter multi-tenancy por **domínio completo** (cada organizador tem seu próprio domínio apontando pro mesmo VPS/aplicação), como já implementado. Um único banco compartilhado entre tenants (não é "banco por tenant"), isolamento feito por `organizer_id` nas tabelas e por queries filtradas — não há schema/database separado por tenant.

## Alternativas consideradas

- **Subdomínio** (`organizador.correvirtual.com.br`). Mais simples de operar (um único certificado wildcard), mas perde a possibilidade de um organizador usar domínio próprio — que é justamente o caso real já seedado (`borafitness.mobspot.com.br`, domínio de terceiro). Descartado.
- **Banco/schema separado por tenant.** Mais isolamento, mas complexidade operacional desproporcional ao tamanho atual (poucos organizadores, um deles real). Descartado por ora; pode ser revisitado numa ADR nova se o número de organizadores crescer muito.

## Consequências

- Cada novo organizador exige: registro em `organizers` + DNS apontando pro VPS + entrada em `server_name` no `docker/nginx/default.conf` + certificado TLS próprio (processo manual hoje, documentado em `runbook.md`; automatizar é candidato de fase 2+).
- **Isolamento entre tenants não é automático em todo lugar** — cada query precisa lembrar de filtrar por `organizer_id` (ou por relação que chegue até ele). A auditoria inicial encontrou pontos onde isso falta (`SubscribeController` não escopa o evento pelo organizador atual; `User.organizer_id` não é preenchido no cadastro) — registrados como BUG-005 e BUG-006 em `backlog.md`. Isso é uma limitação conhecida do estado atual, não do modelo em si.
- Ambiente local usa um atalho (primeiro organizador do banco) para não exigir configuração de domínio — ver `arquitetura.md`.
