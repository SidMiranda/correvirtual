@props(['arte'])

{{--
    Cartaz de uma prova já realizada.

    Não é link nem tem botão: a prova acabou, não há nada para o visitante
    fazer com ela. Ela está aqui como prova do trabalho do organizador para
    quem chegou agora — por isso só a arte, em cor cheia.
--}}

<figure class="arte-realizada">
    <img src="{{ $arte['url'] }}"
         alt="Cartaz do evento {{ $arte['nome'] }}"
         class="arte-realizada__imagem"
         loading="lazy">
</figure>
