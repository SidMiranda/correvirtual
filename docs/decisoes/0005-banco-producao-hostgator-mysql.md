# 0005 — Banco de produção migra pra MySQL/MariaDB gerenciado na Hostgator

Status: Aceita

## Contexto

O ADR 0004 tinha deixado uma pergunta em aberto ("Nota 2026-07-28"): havia menção incerta de que o banco de produção poderia acabar na Hostgator, contradizendo parcialmente o ADR 0001 (Postgres em container no mesmo VPS da aplicação). Em 2026-08-02 o dono do projeto resolveu essa pergunta diretamente: criou dois bancos MySQL gerenciados na Hostgator (`webcit29_eventos_prod` e `webcit29_eventos_dev`) e decidiu consolidar tudo lá — **nenhum ambiente usa mais banco local**.

## Decisão

- **A aplicação continua rodando no VPS via Docker Compose** (ADR 0004 não muda nisso — só o banco sai do container local).
- **Banco de produção**: MySQL 5.7 gerenciado na Hostgator, schema `webcit29_eventos_prod`.
- **Banco de desenvolvimento** (inclusive local, na máquina de quem desenvolve): MySQL gerenciado na Hostgator, schema `webcit29_eventos_dev` — não existe mais Postgres local nem em produção.
- Verificado antes de migrar: as 11 migrations existentes usam só `Schema::Blueprint` genérico, sem nenhuma sintaxe específica do Postgres (sem `DB::statement`/`DB::unprepared`, sem tipos `jsonb`/array, sem `ILIKE`) — portabilidade confirmada sem mudança de código. `config/database.php` já tinha o bloco `mysql` completo com `prefix_indexes => true`, que evita o erro clássico de "chave de índice muito longa" do MySQL 5.7 com `utf8mb4` sem precisar de `Schema::defaultStringLength()`.
- `docker/php/Dockerfile` ganha a extensão `pdo_mysql` mantendo `pdo_pgsql` por enquanto (remoção do driver Postgres fica pra depois de tudo validado em produção, não é urgente).

## Alternativas consideradas

- **Manter Postgres em container, só apontar produção pra um Postgres gerenciado.** Descartada porque a Hostgator (hospedagem compartilhada/cPanel) só oferece MySQL/MariaDB gerenciado, não Postgres.
- **Postgres local pra dev, MySQL só em produção.** Foi a suposição inicial deste ADR antes de confirmar com o dono do projeto — descartada explicitamente: ele quer os dois ambientes (dev e prod) na Hostgator, nada local, pra eliminar a divergência entre "funciona no meu Postgres" e "quebra no MySQL de produção".

## Consequências

- Reintroduz o padrão de risco que o ADR 0001 tinha descartado conscientemente (dependência de banco gerenciado externo — foi exatamente esse tipo de dependência que causou a perda do banco de produção original). Aceito pelo dono do projeto; mitigação mínima: confirmar rotina de backup automático da Hostgator, ou configurar um `mysqldump` agendado a partir do VPS. Ainda não implementado — vira item de backlog.
- `docker-compose.yml` do VPS não precisa mais do serviço `db` — a limpeza completa (remover o serviço do compose, ajustar `reset-dev.sh`) ainda não foi feita; por ora o serviço permanece definido mas não é usado por nenhum ambiente.
- MySQL 5.7.44 (versão oferecida pela Hostgator) está fora do ciclo de suporte da Oracle desde outubro de 2023 — não é algo que o projeto controla (é a versão que a hospedagem compartilhada oferece), só registrado aqui como fato conhecido.
- Charset padrão dos schemas na Hostgator é `utf8`/`utf8_unicode_ci` (não `utf8mb4`), mas isso não afeta as tabelas que o Laravel cria — `config('database.connections.mysql.charset')` já força `utf8mb4` por tabela, independente do padrão do schema.
