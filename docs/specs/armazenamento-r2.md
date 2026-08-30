# Armazenamento de arquivos no Cloudflare R2

Status: Estrutura criada — migração de código não iniciada

## Problema

Hoje todo arquivo do sistema mora dentro do container, em `src/public/images/` — 18 arquivos, 6,1 MB: logo e banners do organizador, banner e card de cada evento, imagens da Home v2 e as imagens padrão de fallback.

Isso tem três problemas concretos:

1. **Arquivo enviado pelo organizador some no deploy.** O deploy roda `docker-compose up -d --build`, que recria o container a partir do código. Qualquer imagem que o painel gravasse no disco do container desapareceria. Hoje isso não morde porque nada é enviado pelo painel — mas o cadastro de eventos que acabou de ser construído vai precisar de upload de banner, e aí morde.
2. **Imagem versionada como se fosse código.** Os 6,1 MB de JPEG estão no Git, e o banner de um evento que o organizador trocar viraria commit.
3. **Não escala para mais de um organizador.** Cada organizador novo engorda a imagem Docker.

Decisão do dono em 2026-08-29: **container é só código**; arquivo vai para o Cloudflare R2 — a mesma conta já usada pelo Cubo (spec 044 de lá) e pelo tunnel/DNS.

## O que já foi feito

- **Bucket `correvirtual-arquivos` criado** na conta R2 (`957864f7…`). Os outros quatro buckets da conta (`cubo-arquivos`, `cubo-backups`, `mia-documentos`, `mobspot-backups`) são de outros projetos e não foram tocados — conferido antes e depois.
- **Os 18 arquivos existentes foram copiados** para a estrutura abaixo, com `Content-Type` correto e `Cache-Control: public, max-age=31536000, immutable`. Download conferido (bytes e assinatura JPEG batem).
- **Os arquivos locais não foram apagados** e **nenhuma linha de código foi alterada** — o site continua servindo tudo de `public/images/` exatamente como antes. A cópia no R2 está parada, esperando a migração de código.

## Estrutura

**Dois buckets, não um.** O desenho inicial tinha um bucket só, com `publico/` e `privado/` como prefixos. Isso estava **errado** e foi corrigido em 2026-08-29 assim que o domínio foi ligado: um domínio público do R2 expõe o **bucket inteiro**, não um prefixo. Com o domínio ativo, `https://<cdn>/privado/...` respondeu **200** — prefixo não é fronteira de segurança. Nada vazou (só havia marcadores vazios), mas documento de atleta ali seria legível por qualquer um que adivinhasse a URL.

```
correvirtual-arquivos/          ← TEM domínio público (cdncorrevirtual.mobspot.com.br)
└── publico/
    ├── organizadores/{organizer_id}/
    │   ├── logo.png · logo.jpg
    │   ├── banner.jpg · banner-mobile.jpg
    │   ├── og.jpg                            ← 1200x630, cartão de link (derivada do banner)
    │   ├── sobre-nos.jpg
    │   ├── equipes/{team_id}/brasao.jpg
    │   ├── realizados/{NN-nome}.jpg          ← vitrine de provas anteriores à plataforma
    │   └── eventos/{event_id}/
    │       ├── banner.jpg
    │       ├── card.jpg
    │       └── og.jpg                        ← 1200x630, derivada do card
    └── plataforma/                           ← não pertence a organizador nenhum
        ├── home/banner-{1,2,3}.jpg
        └── padrao/{banner,card,user,og}.jpg  ← fallback quando falta imagem

correvirtual-privado/           ← NÃO tem domínio nenhum; só com credencial, pelo back
├── organizadores/{organizer_id}/
│   ├── relatorios/                           ← exportação de inscritos gerada pelo painel
│   └── eventos/{event_id}/                   ← comprovante de desafio virtual, etc.
└── atletas/{user_id}/                        ← documento pessoal
```

**`og.jpg` é derivada, nunca enviada.** Quem cadastra manda uma arte só; o cartão de pré-visualização de link precisa de outro formato (1200x630, deitado) e outro peso (abaixo de 300 KB, senão o WhatsApp não monta a prévia). Ela é gerada por `App\Support\ImagemOg` na hora do upload, e `php artisan og:gerar` refaz as que já existem. Ver `docs/specs/frontend-publico.md` (Fase 2).

**`realizados/` é vitrine, não evento.** São as artes de provas que o organizador entregou antes desta plataforma existir — sem inscrição, sem preço e sem página. A lista mora em `config/galeria.php`, por organizador.

O prefixo `publico/` continua existindo dentro do bucket público mesmo ele sendo inteiro público — é o que permite a `ARQUIVOS_BASE_URL` terminar em `/publico` e o disco local espelhar a mesma árvore.

**Regra que vale para sempre:** nada entra em `correvirtual-arquivos` que não possa ser lido por qualquer pessoa da internet. Se está em dúvida, vai no bucket privado.

Duas escolhas de desenho que valem explicar:

**O organizador é o primeiro segmento do caminho.** Isolamento entre organizadores é o ponto fraco conhecido deste projeto (BUG-005 é exatamente um vazamento entre tenants). Com o `organizer_id` na frente, um caminho fora do escopo salta aos olhos na revisão, e apagar um organizador é apagar um prefixo só. Evento fica aninhado sob o organizador dono dele.

**`publico/` e `privado/` separados na raiz** para que uma política de acesso público (domínio próprio ou `r2.dev`) possa valer para `publico/` sem nunca alcançar `privado/`. Se a divisão fosse por tipo de arquivo, cada regra nova exigiria revisar a árvore inteira.

**Nomes normalizados:** `events/1/banner-carnarun-2025.jpg` virou `eventos/1/banner.jpg`. O ID do evento já está no caminho — repetir o nome do evento no arquivo era redundante e é o motivo de existir a coluna `events.banner_url`, que passa a não ter função (ver "Consequências").

## Fora de escopo agora

- Apagar `src/public/images/` — só depois da migração de código validada, e com ordem do dono.
- Backup do banco indo para o R2. O backup diário existe e roda na VPS (`docs/runbook.md`); mandar uma cópia para um bucket é melhoria óbvia, mas é outra tarefa.
- Upload de imagem pelo painel administrativo — depende desta migração, vem depois.
- Redimensionamento/otimização de imagem no envio.

## Centralização do código — feito em 2026-08-29

O bloqueio descrito abaixo foi resolvido. O que mudou:

- **`src/public/images/` foi reorganizado para espelhar o bucket exatamente.** Os dois lados têm hoje a mesma árvore (`organizadores/{id}/eventos/{id}/card.jpg`, `plataforma/padrao/card.jpg`, …). É isso que faz a virada de chave ser uma variável e não uma refatoração.
- **`App\Support\Arquivos`** é o único lugar que monta URL de imagem. Antes a regra estava copiada em seis views, com variações.
- **`config/arquivos.php` + `ARQUIVOS_BASE_URL`**: vazio serve do disco local; preenchido serve do CDN. Nada mais muda.
- **A existência da imagem de evento passou a ser decidida pelo banco** (`banner_url` preenchido) e não pelo disco, com `onerror` no `<img>` cobrindo o arquivo faltando. É o que permite servir de um lugar que não dá para consultar com `file_exists`.
- **DEBT-009 corrigido de passagem**: o favicon montava `images/organizers/{id}logo.png` — faltava a barra, e dava 404 em todas as páginas desde sempre.
- Um teste (`test_nenhuma_view_monta_caminho_de_imagem_na_mao`) varre as views e falha se alguém voltar a usar `file_exists(public_path(...))` ou `asset('images/...')`. Sem ele, a próxima view escrita fora do padrão quebraria o CDN em silêncio.

**Ainda não virado:** `ARQUIVOS_BASE_URL` está vazio em todos os ambientes, então tudo continua sendo servido do disco, como antes. Falta só decidir o domínio (abaixo).

## O que falta para o código usar o R2

### 1. ~~O bloqueio real: `file_exists()` nas views~~ (resolvido acima)

Registro do que era o problema, para não se repetir:

Três views decidem se mostram a imagem ou o fallback consultando o **disco local**:

```php
// resources/views/components/app/event-card.blade.php:6-7
$imageRelativePath = 'images/events/' . $event->id . '/card-' . $event->banner_url;
$hasImage = $event->banner_url && file_exists(public_path($imageRelativePath));
```

O mesmo padrão está em `my-subscriptions.blade.php` e `main-banner.blade.php`. Com o arquivo no R2, `file_exists` responde `false` sempre e **todo mundo cai no fallback** — o site não quebra, fica só sem imagem nenhuma, que é pior de perceber.

A troca é centralizar isso num único lugar (um helper ou um componente Blade) que devolva a URL final e saiba decidir o fallback sem tocar o disco. Hoje a regra está copiada em três views, e é por isso que a migração exige uma mudança de código, não só de configuração.

### 2. ~~Como o navegador chega no arquivo~~ — resolvido em 2026-08-29

**`https://cdncorrevirtual.mobspot.com.br`**, ligado ao bucket `correvirtual-arquivos`, com certificado da Cloudflare já ativo e cache confirmado (`cf-cache-status: HIT`).

Por que num domínio do `mobspot.com.br` e não em `cdn.correvirtual.com.br`: domínio próprio no R2 exige a zona hospedada na Cloudflare, e `correvirtual.com.br` está na WebCit (`ns1/ns2.webcitep.com.br`). A conta Cloudflare tem só `mobspot.com.br`. Mover a zona do `correvirtual.com.br` é possível, mas mexe no MX/SPF do e-mail — que é justamente o remetente das confirmações de inscrição — e o dono preferiu não tocar nisso agora. O nome `cdncorrevirtual` (em vez de só `cdn`) é escolha dele, para o subdomínio dizer de qual produto é.

Consequência aceita de olhos abertos: o endereço `cdncorrevirtual.mobspot.com.br` aparece no `src` das imagens, visível para quem olhar o código-fonte da página. Não aparece na barra de endereço. Trocar depois para um domínio do `correvirtual.com.br` é mudar `ARQUIVOS_BASE_URL` — nenhuma linha de código.

Para o bucket privado a resposta continua sendo "pelo back, autenticado" — e agora isso é garantido pela estrutura, não pela disciplina: ele não tem domínio nenhum.

### 3. Credencial própria

A migração foi feita com a credencial R2 do Cubo, que enxerga **todos** os buckets da conta. Para o runtime deste projeto, criar no painel da Cloudflare um token de R2 restrito só a `correvirtual-arquivos` e colocar em `R2_*` no `.env` daqui. Enquanto isso não é feito, o projeto não tem credencial própria configurada — e não precisa ter, porque nenhum código ainda lê do R2.

## Plano de testes

Quando a migração de código acontecer:

- A URL de imagem de evento é montada a partir do organizador e do evento certos — evento do organizador A nunca aponta para caminho do B.
- Faltando a imagem, cai no fallback de `publico/plataforma/padrao/` (e o teste prova que cai, já que hoje o fallback silencioso é o risco).
- Arquivo em `privado/` não é acessível sem autenticação.
- Upload pelo painel grava no caminho do organizador logado, e não aceita caminho vindo do formulário.

## Consequências

- **`events.banner_url` perde a função.** Ela guarda só o sufixo do nome do arquivo (`carnarun-2025.jpg`) porque as views montavam `card-{banner_url}`. Com o caminho derivado do ID do evento, a coluna vira ou um booleano "tem imagem?" ou nada. Não foi removida — decisão pendente.
- Os eventos 4, 5 e 6 (demonstração do organizador Borafitness) têm `banner_url` preenchido apontando para arquivos que **nunca existiram** no disco. Já caem no fallback hoje; nada muda para eles.
- `images/default/banner-mobile.jpg` é referenciado por `main-banner.blade.php` e não existe — falha pré-existente, não introduzida aqui.
- A conta R2 passa a ter cinco buckets de quatro projetos diferentes. Vale confirmar se o plano gratuito (10 GB) comporta o conjunto; hoje o total é bem abaixo disso.
