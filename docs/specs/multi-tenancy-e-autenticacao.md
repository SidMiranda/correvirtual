# Multi-tenancy (organizador por domínio) e autenticação

Status: Baseline (descreve o comportamento **atual**, bugs incluídos — ver `docs/backlog.md`)

## Problema

Cada organizador tem seu próprio domínio; a plataforma precisa saber, em cada request, "qual organizador é esse" — e um atleta precisa se cadastrar/logar pra se inscrever em eventos.

## Modelos envolvidos

- `Organizer` (`name`, `slug`, `domain` único, `email`, `cnpj`, `active`)
- `User` (`organizer_id` nullable, `name`, `email` único, `cpf` único, `phone`, `birth_date`, `sex`, `email_verified_at`, `email_verification_code`, `password`, `role` [`super_admin`,`organizer_admin`,`athlete`], `active`)

## Fluxo atual: resolução do organizador

Middleware `App\Http\Middleware\IdentifyOrganizerByDomain`, registrado globalmente. Ver diagrama em `docs/arquitetura.md`. Resumo:

- Ambiente `local` + host em `localhost`/`127.0.0.1`/rede local (`192.168.*`/`10.*`) → pega o **primeiro** `Organizer` do banco.
- Qualquer outro caso → busca `Organizer::where('domain', $host)->first()`.
- Não encontrou → `abort(404)`.
- Encontrou → compartilha via `app('currentOrganizer')`, `View::share(...)` e `$request->merge(['current_organizer_id' => ...])`.

## Fluxo atual: cadastro

`POST /register` → `RegisterController::register`. Valida nome, data de nascimento, sexo, telefone, e-mail único, CPF único (11 dígitos, sem formatação), senha (mínimo 6 caracteres — frouxo de propósito por enquanto, comentário no próprio código já reconhece isso como TODO). Cria o `User` com `role = athlete`, gera código de verificação de 4 dígitos, envia por e-mail, guarda o e-mail na sessão pra tela de verificação.

**Bug (BUG-006):** não seta `organizer_id` no `User` criado, mesmo `app('currentOrganizer')` estando disponível nesse momento (o middleware já rodou). O usuário fica sem vínculo de tenant.

## Fluxo atual: login

`POST /login` → `LoginController::login`. Aceita e-mail ou CPF no mesmo campo (detecta qual é pelo formato). Se autenticado mas e-mail não verificado, gera novo código, reenvia, desloga e manda pra tela de verificação. **Não há nenhuma checagem de que o usuário pertence ao organizador do domínio atual** — como `organizer_id` nem é preenchido no cadastro (BUG-006), isso hoje é consistente (nem faria sentido checar um campo que nunca é setado), mas significa que, na prática, uma mesma conta pode logar e se inscrever em eventos de organizadores diferentes pelo mesmo domínio raiz. Se isso é desejado (conta única entre tenants) ou não é uma decisão de produto ainda em aberto — não decidir isso silenciamente ao corrigir BUG-006.

Sem rate limiting (BUG-007).

## Fluxo atual: verificação de e-mail

`GET/POST /verify-email` → `VerifyEmailController`. Confirma o código de 4 dígitos contra `email_verification_code`, marca `email_verified_at`.

## Bugs conhecidos nesta área

Ver `docs/backlog.md`: BUG-006 (`organizer_id` não preenchido no cadastro), BUG-007 (sem throttle em login/registro/verificação).

## Fora de escopo hoje

- Papéis `organizer_admin` e `super_admin` existem no enum mas não têm nenhuma tela/permissão associada — só `athlete` é usado de fato.
- Autocadastro de organizador (hoje é manual, ver `runbook.md`).
- Recuperação de senha (`PasswordResetController` existe e tem rota implícita pelo nome, mas não foi auditado em detalhe nesta rodada — revisar ao tocar nesta área).

## Plano de testes (a escrever ao corrigir os bugs desta área)

- Middleware resolve o organizador certo por domínio, e 404 quando o domínio não tem organizador cadastrado.
- Cadastro seta `organizer_id` igual ao organizador do domínio atual (depende de BUG-006 corrigido e da decisão de produto sobre conta única vs. por tenant).
- Login/registro/verificação respeitam rate limit após N tentativas (depende de BUG-007 corrigido).
- CPF e e-mail duplicados são rejeitados no cadastro.
