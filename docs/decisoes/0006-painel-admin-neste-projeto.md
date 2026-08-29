# 0006 — Painel administrativo fica neste projeto, não no Cubo

Status: Aceita

## Contexto

Em 2026-08-29 o dono do projeto levantou a hipótese de tirar o cadastro de eventos daqui e levá-lo para o **Cubo** (outro produto dele: plataforma multi-tenant Laravel + Vue, com controle de acesso, permissões finas e módulos plugáveis já prontos). O Cubo inclusive já tem um módulo `Eventos` registrado, só com o manifesto — nenhuma tabela, nenhuma tela.

O argumento a favor era real: o Cubo já resolve login, papéis e permissões, que aqui não existem. O painel administrativo é justamente a parte deste projeto que mais precisa disso, e seria retrabalho construir de novo.

O levantamento feito antes da decisão (registrado no briefing de 2026-08-29) apontou três fatos que pesaram contra:

1. **O Cubo não tem servidor.** Ele roda na máquina do dono, exposto por um túnel da Cloudflare — não há ambiente de produção. O cadastro de eventos só funcionaria enquanto essa máquina estivesse ligada, o que inviabiliza o organizador (hoje o Uéslei) mexer sozinho.
2. **Os dois sistemas separam clientes de formas diferentes.** Aqui é `organizer_id`, resolvido pelo domínio da requisição (ADR 0002). No Cubo é `empresa_id`, via Global Scope. Casar os dois exige uma camada de tradução que não existe hoje.
3. **O volume não justifica.** O banco inteiro de produção tem 6 eventos, 4 usuários e 8 inscrições. Não há escala que compense o custo de operar dois sistemas acoplados.

## Decisão

**O painel administrativo é construído neste projeto**, na VPS onde a aplicação já roda, com a stack que já existe (Laravel 12 + Blade + o template SB Admin Pro já recebido em `TEMPLATES/Painel-Admin/`).

O endereço previsto é `admin.correvirtual.com.br`, apontando para a mesma VPS (`143.95.218.62`) e servido pela mesma aplicação — não é um segundo deploy.

Autorização: o papel `organizer_admin`, que já existe no enum de `users.role` desde a migration original e nunca foi usado. Dentro do painel, o escopo é o `organizer_id` **do usuário logado**, não o domínio — diferente do site público. O detalhamento está em `docs/specs/painel-admin.md`.

## Alternativas consideradas

- **Levar o cadastro para o módulo `Eventos` do Cubo.** Descartada pelos três motivos acima, sendo a falta de servidor do Cubo o impeditivo prático.
- **Compartilhar o banco entre os dois sistemas** (o Cubo escrevendo, este projeto lendo). Descartada junto com a anterior — resolveria a duplicação de dados, mas amarraria a disponibilidade do site público à do Cubo, que hoje é uma máquina de trabalho.
- **`eventos.correvirtual.com.br/admin` em vez de subdomínio próprio.** Não precisaria de DNS nem de certificado novo. Descartada por preferência do dono por um endereço separado; o código funciona nos dois formatos de qualquer jeito (a autorização não depende do domínio), então voltar atrás é barato.

## Consequências

- Este projeto passa a ter dois públicos com regras diferentes de escopo: o site (organizador vem do domínio) e o painel (organizador vem do usuário). `IdentifyOrganizerByDomain` ganha uma exceção para as rotas do painel — sem ela, `admin.correvirtual.com.br` cairia no 404 de "organizador não encontrado".
- Papéis e permissões vão precisar ser construídos aqui, do zero e de forma simples (um papel, sem permissões finas por tela). Se isso crescer, a decisão merece ser revisitada numa ADR nova.
- `admin.correvirtual.com.br` depende de um registro DNS que só o dono pode criar (nameservers `ns1/ns2.webcitep.com.br`) e de um certificado TLS próprio, emitido do mesmo jeito que o atual (certbot no host, ver `docs/runbook.md`).
- O módulo `Eventos` do Cubo fica sem uso. Não é apagado — só não recebe trabalho.
