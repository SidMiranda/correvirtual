# Runbook

## Rodar localmente

Pré-requisito: Docker Desktop rodando.

```bash
cp .env.example .env               # credenciais do container Postgres
cp src/.env.example src/.env       # config do Laravel (já vem casada com o .env acima)

docker compose -f docker-compose.yml -f docker-compose.local.yml up -d --build
docker exec corre_app composer install
docker exec corre_app php artisan key:generate
docker exec corre_app php artisan storage:link

# storage/framework/* e storage/logs não existem no checkout (dirs vazios não vão pro
# git) — sem isso a aplicação responde 500 na primeira subida:
docker exec -u root corre_app sh -c "mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/app/temp storage/logs bootstrap/cache && touch storage/logs/laravel.log && chmod -R 777 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"

docker exec corre_app php artisan migrate:fresh --seed
```

Acesse `http://localhost`. Em ambiente `local`, o middleware `IdentifyOrganizerByDomain` resolve automaticamente o **primeiro** organizador do banco para `localhost`/`127.0.0.1`/IPs de rede local — não precisa configurar domínio.

**Por que `-f docker-compose.yml -f docker-compose.local.yml`:** a config de produção do nginx (`docker/nginx/default.conf`) exige certificados TLS reais do Let's Encrypt e nunca sobe numa máquina local sem eles. `docker-compose.local.yml` troca só o `nginx` para servir HTTP puro (`docker/nginx/local.conf`) — é um overlay 100% opcional que a produção nunca lê (o deploy roda `docker-compose up` sem `-f` nenhum).

Scripts `reset-dev.sh` (Linux/WSL) e `reset-dev.bat` (Windows) automatizam todo esse processo do zero (derrubam containers, sobem de novo, reinstalam dependências, ajustam permissões, recriam o banco) — **use-os no dia a dia em vez dos comandos manuais acima**, que existem aqui só pra documentar o que cada script faz por baixo dos panos.

### Variáveis de ambiente

Dois arquivos `.env` diferentes, que precisam concordar entre si:

| Arquivo | Para quê | Variáveis-chave |
|---|---|---|
| `.env` (raiz) | Bootstrap do container Postgres (+ tunnel, se usado) | `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD`, `CLOUDFLARE_TUNNEL_TOKEN` |
| `src/.env` | Configuração do Laravel | `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (devem ser **iguais** aos `POSTGRES_*` acima), `DB_HOST=db`, `DB_PORT=5432`, `MERCADOPAGO_ACCESS_TOKEN`, `MERCADOPAGO_WEBHOOK_SECRET`, `APP_URL` |

Se os dois arquivos ficarem dessincronizados, o Postgres sobe com uma senha e o Laravel tenta conectar com outra — o container `app` fica em loop de erro de conexão. Se isso acontecer: `docker compose down -v` (apaga o volume do Postgres) e suba de novo com os dois `.env` corrigidos.

### Acessar o Postgres diretamente

A porta 5432 não é publicada no host de propósito. Para usar um client (DBeaver, psql, etc.) localmente:

```bash
docker exec -it corre_db psql -U corre_virtual -d corre_virtual
```

### Expor o ambiente local na internet (Cloudflare Tunnel)

Pra testar em outro dispositivo, mandar link de revisão pro organizador, etc. — sem isso ser o deploy de produção. Setup (uma vez, no [painel Zero Trust da Cloudflare](https://one.dash.cloudflare.com/) → Networks → Tunnels): criar um tunnel, tipo "Cloudflared", copiar o token, e configurar um Public Hostname apontando pra `http://nginx:80` (nome do serviço no docker-compose — não `localhost`, o cloudflared roda dentro da mesma rede Docker). Cole o token em `CLOUDFLARE_TUNNEL_TOKEN` no `.env` da raiz, depois:

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml -f docker-compose.tunnel.yml up -d
```

`docker-compose.tunnel.yml` é 100% opt-in — só sobe se você incluir esse `-f` explicitamente.

Pra testar o Pix de ponta a ponta com esse tunnel (webhook do Mercado Pago batendo no seu ambiente local), configure a URL pública do tunnel como "URL de notificação" no [painel do Mercado Pago](https://www.mercadopago.com.br/developers/panel) e copie o secret que ele gera pra `MERCADOPAGO_WEBHOOK_SECRET` em `src/.env`. Sem esse secret configurado, `/api/webhooks/mercadopago` rejeita toda notificação com `401` (falha fechada — ver `docs/specs/pagamentos-pix.md`).

### Verificação visual (Playwright MCP)

Pra tirar screenshot/navegar na aplicação de verdade (não só `curl`), o projeto usa o servidor MCP do Playwright, registrado em `.mcp.json` (raiz, versionado). Exige Node.js instalado no host — se `claude mcp list` não mostrar `playwright` conectado, rode `/mcp` numa sessão do Claude Code pra aprovar/depurar.

## Deploy

`main` tem deploy automático via `.github/workflows/deploy.yml`: a cada push, o workflow conecta por SSH no VPS, escreve `src/.env` a partir do secret `APP_ENV`, deriva o `.env` da raiz (credenciais do Postgres) a partir dos valores `DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD` que estiverem nesse `.env`, e roda `docker-compose up -d --build` + `migrate --force` + `optimize`.

**Ação necessária uma única vez:** garanta que o secret `APP_ENV` no GitHub tenha `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` apontando para credenciais Postgres válidas (não as antigas do MySQL/provedor perdido) — é a partir dele que tanto o Laravel quanto o container do Postgres em produção são configurados.

**Primeiro deploy depois da retomada:** como o banco de produção foi perdido, depois do primeiro deploy bem-sucedido é preciso popular o banco manualmente uma vez (o deploy só roda `migrate`, não `seed`, de propósito — pra não duplicar dados em deploys seguintes):

```bash
ssh <usuario>@<host>
docker exec corre_app php artisan db:seed --force
```

### Branches

`main` (protegida, deploy automático) ← PR ← `develop` (integração, sem deploy automático) ← PR ← `feature/*` / `fix/*`. Ver ADR 0004.

## Cadastrar um novo organizador (tenant)

Ainda não existe painel para isso (fase 2 — ver `backlog.md`). Hoje é manual:

1. Criar o registro em `organizers` (via tinker, seeder ou `psql`) com `domain` = o domínio que vai apontar pra esse organizador.
2. Apontar o DNS desse domínio para o VPS.
3. Adicionar o domínio em `server_name` no `docker/nginx/default.conf` e emitir certificado TLS (Let's Encrypt) pra ele.
4. Cadastrar os eventos desse organizador (hoje também manual/seeder).

## Troubleshooting

- **`app` não sobe / erro de conexão com banco:** ver seção de variáveis de ambiente acima.
- **Mudança em `.env` não é refletida:** Laravel cacheia config em produção. Rode `docker exec corre_app php artisan config:clear` (ou `optimize`, que já roda no deploy).
- **Erro 404 "Organizador não encontrado":** o host da requisição não bate com nenhum `organizers.domain` no banco — confira o seeder ou o registro manual.
- **Testes:** `docker exec corre_app php artisan test`. Rodam contra sqlite em memória (`phpunit.xml`), não tocam no Postgres — não precisa de setup extra.
