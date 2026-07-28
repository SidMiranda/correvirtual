# 0001 — Postgres como banco de dados

Status: Aceita

## Contexto

O banco de dados de produção original (MySQL, num provedor externo) foi perdido junto com o acesso a esse provedor. O `docker-compose.yml` ficou sem nenhum serviço de banco. O projeto precisa ser reerguido do zero de qualquer forma — não há dados para migrar.

## Decisão

Adotar PostgreSQL 16 como único banco, rodando em container (`corre_db`, imagem `postgres:16-alpine`), tanto em desenvolvimento quanto em produção (mesmo VPS, ver ADR 0004). `config/database.php` do Laravel já trazia um bloco `pgsql` completo, e as migrations existentes usam `Schema::Blueprint` genérico — nenhuma delas depende de sintaxe específica do MySQL, então a portabilidade é direta.

## Alternativas consideradas

- **Recriar em MySQL/MariaDB**, mantendo o que já existia. Descartado: já que o banco precisa ser recriado do zero mesmo, não há custo de migração, e Postgres foi a preferência explícita para esta retomada.
- **Banco gerenciado externo** (RDS, Neon, Supabase, etc.). Descartado por ora: adiciona custo e mais uma credencial/provedor externo para gerenciar — exatamente o tipo de dependência que causou a perda do banco anterior. Container no mesmo VPS que já roda a aplicação é mais simples de operar para o tamanho atual do projeto (um organizador, poucos eventos).

## Consequências

- `docker/php/Dockerfile` precisa da extensão `pdo_pgsql` (trocada no lugar de `pdo_mysql`).
- Backup do banco passa a ser responsabilidade de quem opera o VPS (não é automático só por estar em container) — ainda não há rotina de backup definida; deve virar item de backlog antes de haver dados reais valiosos no banco.
- Enums do Laravel (`$table->enum(...)`) são implementados pelo Postgres como `varchar` + `CHECK constraint`, diferente do `ENUM` nativo do MySQL — funciona da mesma forma do ponto de vista do Eloquent, sem mudança de código necessária.
