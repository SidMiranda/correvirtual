# TEMPLATES

Pasta pra templates de referência que vão guiar a reconstrução do frontend. Não é código do projeto — é material de referência (visual/estrutura) que a IA vai olhar antes de propor o plano de reconstrução de cada parte.

## Subpastas

- `TEMPLATES/Front-End/` — template do site público (HTML/CSS/JS estático: home, about, contato, guia de tipografia). Primeira prioridade, parte do MVP. Já recebido — vai virar insumo do spec `docs/specs/frontend-publico.md` (a escrever) que descreve como adaptar essas páginas às rotas/views Blade existentes (lista de eventos, detalhe do evento, inscrição). O formulário de contato do template usa PHP+reCAPTCHA próprio (`Front-End/bat/`) — plausivelmente substituível pelo Mail do Laravel já usado no projeto (`app/Mail/VerifyEmailCode.php`); decisão fica para o spec, não aqui.
- `TEMPLATES/admin-sbadmin2/` — template SB Admin 2 (Bootstrap) pro painel administrativo do organizador. Fase 2, ainda não começou (ver `docs/backlog.md`).

Nenhum arquivo de frontend do projeto (`src/resources/views`, `src/public/css`, `src/public/js`) foi tocado ainda a partir deste template — isso segue o processo normal do `CLAUDE.md` (spec → plano aprovado → implementação).
