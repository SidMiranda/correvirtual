# TEMPLATES

Pasta pra templates de referência que vão guiar a reconstrução do frontend. Não é código do projeto — é material de referência (visual/estrutura) que a IA vai olhar antes de propor o plano de reconstrução de cada parte.

## Subpastas

- `TEMPLATES/Front-End/` — template do site público (HTML/CSS/JS estático: home, about, contato, guia de tipografia). Primeira prioridade, parte do MVP. Já recebido — vai virar insumo do spec `docs/specs/frontend-publico.md` (a escrever) que descreve como adaptar essas páginas às rotas/views Blade existentes (lista de eventos, detalhe do evento, inscrição). O formulário de contato do template usa PHP+reCAPTCHA próprio (`Front-End/bat/`) — plausivelmente substituível pelo Mail do Laravel já usado no projeto (`app/Mail/VerifyEmailCode.php`); decisão fica para o spec, não aqui.
- `TEMPLATES/Painel-Admin/` — template "SB Admin Pro" (StartBootstrap, família SB Admin 2) pro painel administrativo do organizador. Já recebido também, inclusive com telas prontas de multi-tenant (`multi-tenant-select.html`, `multi-tenant-create.html`, `multi-tenant-join.html`, `multi-tenant-add-users.html`) que encaixam bem no modelo de organizador por domínio já existente. **Fase 2 — só começa depois do MVP (site público) estar no ar**, ver `docs/backlog.md`.

Nenhum arquivo de frontend do projeto (`src/resources/views`, `src/public/css`, `src/public/js`) foi tocado ainda a partir deste template — isso segue o processo normal do `CLAUDE.md` (spec → plano aprovado → implementação).
