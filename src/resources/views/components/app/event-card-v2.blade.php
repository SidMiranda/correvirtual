@props(['event'])

{{--
    Card v2: só a arte do evento.

    O cartaz que o organizador manda fazer já traz nome, data, local, distâncias
    e patrocinador — repetir tudo isso em texto embaixo era dizer duas vezes a
    mesma coisa e obrigar a arte a caber num pedaço pequeno.

    Aqui o cartaz ocupa o card inteiro e o card inteiro é o link para a página do
    evento. Sobra por cima só o botão de compartilhar.

    O modelo antigo continua em `event-card` e volta trocando CARD_DE_EVENTO
    para v1 no .env (ver config/aparencia.php).
--}}

@php
    $mensagem = "Confira este evento: *" . $event->title . "*\n📍 " . $event->location . "\n\n" . url('/event/' . $event->id);
    $whatsapp = "https://api.whatsapp.com/send?text=" . urlencode($mensagem);
@endphp

<div class="cartaz" style="background: {{ $event->degrade() }};">
    <a class="cartaz__link" href="{{ url('/event/' . $event->id) }}"
       aria-label="Ver o evento {{ $event->title }}">

        @if($event->banner_url)
            <img src="{{ \App\Support\Arquivos::cardDoEvento($event) }}"
                 alt="{{ $event->title }}" class="cartaz__arte" loading="lazy">
        @else
            {{-- Sem arte, o cartaz vira o degradê do evento com o nome — assim a
                 grade não fica com um buraco. --}}
            <span class="cartaz__nome">{{ $event->title }}</span>
        @endif
    </a>

    <button type="button" class="cartaz__compartilhar"
            onclick="window.open('{{ $whatsapp }}', '_blank', 'noopener');"
            title="Compartilhar no WhatsApp" aria-label="Compartilhar no WhatsApp">
        <i data-feather="share-2"></i>
    </button>
</div>
