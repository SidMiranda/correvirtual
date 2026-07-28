# 0003 — SDD e fluxo de trabalho com IA

Status: Aceita

## Contexto

O projeto vai ser desenvolvido daqui pra frente com apoio intenso de IA (Claude Code). Sem estrutura, esse tipo de trabalho tende a gerar código que ninguém revisou de verdade, decisões que não ficam registradas em lugar nenhum, e regressões silenciosas em fluxos críticos (o de pagamento, por exemplo). O projeto também não tinha nenhuma documentação viva antes desta rodada.

## Decisão

Adotar **Specification-Driven Development (SDD)**: comportamento relevante é descrito em `docs/specs/` antes de ser implementado (ou, para o que já existe, documentado como "baseline" descrevendo o comportamento atual, bugs incluídos). Junto com isso:

- `CLAUDE.md` na raiz define as regras operacionais da IA: ler a documentação relevante no início da sessão, **nunca criar/alterar arquivo sem plano aprovado antes**, tratar teste como parte da entrega (não opcional), e manter `CHANGELOG.md`/`backlog.md` atualizados a cada mudança.
- Decisões arquiteturais viram ADR (este arquivo é um exemplo).
- Specs, ADRs, backlog e changelog vivem no próprio repositório, versionados junto com o código — não em uma ferramenta externa.

## Alternativas consideradas

- **Só usar issues/board externo (Trello, Linear, etc.) para backlog.** Descartado por ora: manter tudo em Markdown versionado no repo custa menos fricção para um projeto desse tamanho e fica acessível à IA sem integração externa. Pode ser revisitado se o projeto crescer e ganhar mais gente.
- **Documentar só quando "sobrar tempo".** É o que já vinha acontecendo (documentação inexistente) — foi explicitamente o que motivou esta ADR.

## Consequências

- Todo trabalho relevante fica mais lento no curto prazo (escrever spec/ADR/atualizar backlog toma tempo) em troca de reduzir retrabalho e permitir que qualquer sessão de IA futura (ou outro desenvolvedor) entenda o "porquê" sem precisar reconstruir contexto do zero.
- Documentação desatualizada é pior que ausência de documentação (engana em vez de não informar) — a responsabilidade de manter os docs em dia é de quem faz a mudança, não uma tarefa de limpeza separada. Isso está explícito no `CLAUDE.md`.
- Specs "baseline" (`eventos-e-inscricoes.md`, `pagamentos-pix.md`, `multi-tenancy-e-autenticacao.md`) documentam o comportamento **atual**, incluindo bugs conhecidos — elas vão precisar ser reescritas conforme os bugs do backlog forem corrigidos, não são a versão "ideal" ainda.
