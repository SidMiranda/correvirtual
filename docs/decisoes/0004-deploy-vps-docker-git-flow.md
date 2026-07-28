# 0004 — Deploy em VPS via Docker + git flow

Status: Aceita

## Contexto

Ao planejar a retomada, surgiu a dúvida sobre o alvo de produção: o `.github/workflows/deploy.yml` já existente faz deploy via SSH + `docker-compose` num VPS, o que pressupõe acesso root/Docker no servidor — mas havia menção a "servidor FTP" na conversa que motivou esta retomada, o que sugeriria hospedagem compartilhada sem Docker. As duas coisas mudam bastante a arquitetura de produção (hospedagem FTP tradicional não roda containers). Perguntado diretamente, foi confirmado: produção é VPS com SSH/Docker, o workflow existente reflete a realidade.

## Decisão

- **Produção continua em VPS com Docker Compose**, deploy automatizado via GitHub Actions + SSH, como já estava implementado. O Postgres (ADR 0001) roda em container nesse mesmo VPS — não é banco gerenciado externo.
- **Modelo de branches** (git flow enxuto, proporcional ao tamanho do projeto — não o git-flow completo com release/hotfix branches formais): `main` protegida com deploy automático ← PR ← `develop` (integração, sem deploy automático) ← PR ← `feature/*` / `fix/*`. Antes desta decisão só existia a branch `main`.

## Alternativas consideradas

- **Hospedagem FTP pura, sem Docker em produção.** Consideraríamos banco gerenciado à parte e pipeline via FTP/rsync no lugar do `docker-compose` remoto. Descartada após confirmação — não é a realidade do servidor atual.
- **Git-flow completo** (branches `release/*` e `hotfix/*` formais). Descartado por desproporcional ao tamanho do time (uma pessoa) e do projeto agora; pode ser adotado depois se a cadência de releases justificar.

## Consequências

- O secret `APP_ENV` do GitHub Actions passa a ser a fonte única de verdade também para as credenciais do Postgres em produção — o workflow deriva o `.env` da raiz (usado pelo `docker-compose` para inicializar o container do banco) a partir das chaves `DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD` que já estão nesse secret. Isso exige que o secret seja atualizado com credenciais Postgres válidas antes do próximo deploy (ver `runbook.md`).
- Sem branch `release`/`hotfix` formais, hotfixes urgentes vão direto numa `fix/*` → PR direto pra `main` quando necessário — aceito conscientemente dado o tamanho do projeto.
- Seeding de produção continua manual na primeira vez após um banco novo (ver `runbook.md`) — o deploy automático nunca roda `db:seed`, só `migrate`, para não duplicar dados em deploys futuros.
