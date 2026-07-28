# Corre Virtual — eventos.correvirtual.com.br

Plataforma de inscrições para eventos de corrida (presenciais e virtuais), multi-tenant por domínio, com pagamento via Pix (Mercado Pago). Backend em Laravel 12 (`src/`), infraestrutura em Docker Compose (app PHP-FPM + Nginx + Postgres).

## Comece por aqui

- **Rodar o projeto localmente:** [`docs/runbook.md`](docs/runbook.md)
- **Entender o domínio (organizador, evento, modalidade, kit, inscrição, pagamento):** [`docs/visao-geral.md`](docs/visao-geral.md)
- **Arquitetura, stack e multi-tenancy:** [`docs/arquitetura.md`](docs/arquitetura.md)
- **O que falta / bugs conhecidos / próximas fases:** [`docs/backlog.md`](docs/backlog.md)
- **Decisões arquiteturais (ADRs):** [`docs/decisoes/`](docs/decisoes/)
- **Specs de comportamento por área:** [`docs/specs/`](docs/specs/)

## Se você é a IA trabalhando nesta sessão

Leia [`CLAUDE.md`](CLAUDE.md) primeiro. Regra número um: nenhum arquivo é criado ou alterado sem um plano apresentado e aprovado antes.

## Início rápido

```bash
cp .env.example .env
cp src/.env.example src/.env
./reset-dev.sh      # Windows: reset-dev.bat
```

Sobe app + nginx (HTTP local, sem TLS) + Postgres, instala dependências e recria o banco com dados de exemplo. Acesse `http://localhost`. Passo a passo manual, variáveis de ambiente, troubleshooting e fluxo de deploy estão no [runbook](docs/runbook.md).
