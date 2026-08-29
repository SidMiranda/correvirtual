# Specs

Cada arquivo aqui descreve o comportamento esperado de uma área do sistema. É a fonte da verdade de *o que o sistema deve fazer* — o código implementa o spec, não o contrário.

## Convenção

- Novo comportamento: escreva o spec (a partir de `_modelo.md`) **antes** do código, valide com o usuário via plano, só então implemente.
- Comportamento existente sem spec: os três specs iniciais (`eventos-e-inscricoes.md`, `pagamentos-pix.md`, `multi-tenancy-e-autenticacao.md`) documentam o estado **atual** do sistema como "baseline" — incluindo bugs conhecidos, marcados explicitamente como tal e referenciados ao `docs/backlog.md`. Ao corrigir um desses bugs, atualize o spec para descrever o comportamento correto (e remova a marcação de bug).
- Spec e código divergiram? Pare e resolva antes de continuar — atualizando o spec (se o código estava certo e o spec desatualizado) ou o código (se foi regressão).

## Specs existentes

| Spec | Área |
|---|---|
| [eventos-e-inscricoes.md](eventos-e-inscricoes.md) | Eventos, modalidades, kits, inscrição |
| [pagamentos-pix.md](pagamentos-pix.md) | Pix / Mercado Pago |
| [multi-tenancy-e-autenticacao.md](multi-tenancy-e-autenticacao.md) | Organizador por domínio, cadastro, login |
| [frontend-publico.md](frontend-publico.md) | Site público, Home v2 |
| [painel-admin.md](painel-admin.md) | Painel do organizador (`/admin`), cadastros |
| [armazenamento-r2.md](armazenamento-r2.md) | Arquivos no Cloudflare R2 (imagens, banners, uploads) |
