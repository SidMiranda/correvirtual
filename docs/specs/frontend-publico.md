# Frontend público — redesign visual (Fase 1: Home v2)

Status: Implementado (aguardando revisão visual do usuário)

## Problema

O site público atual (`src/resources/views/index.blade.php` e componentes em `components/app/`) é funcional mas visualmente pobre: o "menu" do topo (`top-bar.blade.php`) é na verdade a barra de admin do template SB Admin reaproveitada (dropdowns de notificação, avatar de usuário — nada disso faz sentido pro visitante público de um site de inscrição pra corrida), o banner principal é uma imagem estática única, e o CSS está espalhado em `<style>` inline por componente sem um sistema de cores coerente.

Em `TEMPLATES/Front-End/` (template de referência já recebido) existem dois elementos que o organizador gostou e quer incorporar:
1. Um menu de duas camadas (`rd-navbar-top-panel` — barra fina em cima com contato/social; `rd-navbar-panel` — barra principal com logo e navegação).
2. Um banner rotativo (`swiper-container`) com várias telas, cada uma com título, texto de apoio e um botão de call-to-action.

A paleta de cores do template (vermelho/escuro genérico) não bate com a identidade do projeto — a decisão foi adaptar pra azul escuro + azul claro, reaproveitando as cores que **já existem** no código hoje: `#0d1b2a` (azul bem escuro, já usado como cor de texto em `about.blade.php`) e `#1a71b2` (azul médio, já usado em botões/links/rodapé em vários componentes).

## Requisitos

- [x] Menu de duas camadas (barra utilitária fina + barra de navegação principal) reaproveitando a estrutura visual do template, recolorido pra azul escuro/claro.
- [x] Banner rotativo com 3 slides, cada um com título, subtítulo e botão(ões) de CTA, avançando automaticamente e com navegação manual (setas + indicadores).
- [x] Sistema de cores centralizado (custom properties CSS) baseado nas cores já em uso no projeto, não uma paleta nova.
- [x] Sem novas dependências JS pesadas (sem jQuery/Bootstrap/Swiper/OWL — tudo em CSS + JS vanilla, ~100 linhas).
- [x] Nenhuma funcionalidade muda: login, logout, "Minhas inscrições", dropdown de usuário continuam nas mesmas rotas, só com visual novo.
- [x] Escopo restrito à Home (`/`) nesta fase — nenhuma outra página (`login`, `subscribe`, `event-details`, etc.) é afetada.

## Fora de escopo (nesta fase)

- Aplicar o redesign nas outras páginas (login, detalhe de evento, inscrição, minhas inscrições) — só acontece se o organizador aprovar a v2 da Home.
- Qualquer mudança de comportamento (regras de inscrição, pagamento, etc.) — só design.
- Reescrever `top-bar.blade.php`/`layouts/app.blade.php` originais — ficam intocados, servindo todas as páginas exceto a Home.

## Design

### Isolamento (por que nada mais quebra)

`layouts/app.blade.php` (usado por login, registro, verificação, inscrição, minhas inscrições, detalhe de evento) **não foi tocado**. A Home usa um layout novo e paralelo:

- `resources/views/layouts/app-v2.blade.php` — mesma estrutura básica (`head`, `response-message`, `scripts`), mas inclui `nav-v2` em vez de `top-bar`.
- `resources/views/components/app/nav-v2.blade.php` — menu de duas camadas.
- `resources/views/components/app/banner-v2.blade.php` — banner rotativo.
- `public/css/home-v2.css` — sistema de cores (custom properties) + estilos do nav/banner/seções da v2.
- `public/js/home-v2.js` — rotação do banner e toggle do menu mobile (vanilla, sem dependências).

`index.blade.php` passou a estender `layouts.app-v2` em vez de `layouts.app`. Todas as outras views continuam em `layouts.app` sem nenhuma alteração.

### Paleta de cores (`public/css/home-v2.css`, `:root`)

| Token | Valor | Origem | Uso |
|---|---|---|---|
| `--cv-navy` | `#0d1b2a` | já usado em `about.blade.php` (`.cv-title`) | barra utilitária, rodapé, texto de destaque |
| `--cv-blue` | `#1a71b2` | já usado em botões/links/rodapé em todo o projeto | barra de navegação principal, CTAs |
| `--cv-blue-light` | `#4fa3d8` | tint calculado a partir de `--cv-blue` | hover, detalhes, gradiente do banner |
| `--cv-blue-pale` | `#eaf4fb` | tint bem claro de `--cv-blue` | fundo de seções alternadas |
| `--cv-ink` | `#1a1a1a` | já usado em `about.blade.php` (`.cv-badge`) | texto principal |
| `--cv-muted` | `#666666` | já usado em `about.blade.php` (`.cv-description`) | texto secundário |

### Menu de duas camadas (`nav-v2.blade.php`)

Revisado depois do primeiro round de feedback visual (o organizador viu nome do organizador e botões "Entrar"/"Criar conta" duplicados nas duas barras — corrigido):

- **Barra utilitária** (`--cv-navy`, ~36px de altura): só uma frase curta (tagline) à esquerda + ícones pequenos de rede social (Instagram, site oficial) à direita. Nada de marca ou autenticação aqui — é só um detalhe fino em cima.
- **Barra principal** (fundo `--cv-blue-pale`, tonalidade azul clara em vez de branco puro, sticky ao rolar): logo/nome do organizador à esquerda, navegação (`Eventos`, `Sobre`, `Patrocinadores`) no centro, e a área de autenticação (Entrar/Criar conta, ou Minhas inscrições/usuário/Sair quando logado) à direita, separada por uma linha divisória sutil. **Toda a autenticação mora só aqui agora** — antes estava duplicada nas duas barras. Em telas pequenas vira menu hambúguer (JS vanilla, só adiciona/remove uma classe), com a área de auth empilhada junto do resto do menu.

### Banner rotativo (`banner-v2.blade.php`)

3 slides com crossfade por CSS (`opacity` + `transition`, sem `transform3d` pesado), avançando a cada 6s, com setas prev/next e indicadores (dots) clicáveis. Cada slide: fundo com foto (ver "Geração de imagens" abaixo) + gradiente escuro por cima pra garantir contraste do texto, título grande, subtítulo, 1-2 botões CTA (ex.: "Ver Eventos", "Inscreva-se Já") apontando pra âncoras/rotas reais da Home.

Se o organizador tiver banner customizado (`images/organizers/{id}/banner.jpg`, já suportado hoje), ele vira o slide 1 (prioridade sobre a imagem do Gemini, por autenticidade) — a lógica de fallback de `main-banner.blade.php` original foi preservada, só a apresentação (rotação/CTA) é nova.

### Geração de imagens (Gemini) — executado

`app/Console/Commands/GenerateGeminiImages.php` rodou com sucesso (`GEMINI_API_KEY` configurada em `src/.env`, não commitada) e gerou `public/images/home-v2/banner-{1,2,3}.jpg` (corredores em grupo, pernas em movimento, comemoração na chegada com medalha — prompts sem texto/logo, pra não competir com o texto sobreposto do banner). Slide 1 usa o banner real do organizador quando existe (com fallback pra `banner-1.jpg` se não existir); slides 2 e 3 usam `banner-2.jpg`/`banner-3.jpg`. Pra gerar de novo (trocar as imagens): `docker exec corre_app php artisan images:generate-gemini --force`.

## Plano de testes

Mudança é puramente visual/apresentação (HTML/CSS/JS), sem lógica de negócio nova — não há teste automatizado PHP para isso. Verificação feita visualmente via Playwright MCP (screenshot desktop + mobile da Home v2) antes de considerar pronto pra revisão. Suite de testes existente (`php artisan test`) continua passando (nenhum controller/model tocado nesta fase).

## Critérios de aceite

- [x] `http://localhost/` carrega a Home v2 sem erro (200), com menu de duas camadas e banner rotativo.
- [x] Nenhuma outra rota (`/login`, `/subscribe/event/{id}`, `/my-subscriptions`, `/event/{id}`) muda de aparência ou comportamento.
- [x] Login/logout/"Minhas inscrições" continuam funcionando a partir do novo menu.
- [x] `php artisan test` continua verde.
- [ ] Organizador revisa e aprova (ou pede ajuste) — **pendente**, é o próximo passo depois desta rodada.
