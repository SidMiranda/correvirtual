@props(['patrocinadores'])

{{--
    Os patrocinadores do organizador, vindos do banco.

    Antes eram seis SVGs de exemplo ("Logoipsum") colados neste arquivo, herdados
    do template — trocar um exigia mexer em código e subir deploy. Agora saem do
    cadastro do painel (App\Models\Sponsor).

    Quem tem site vira link; quem não tem fica só como imagem. Sem logo enviado,
    aparece o nome em texto — melhor que um buraco na fileira.
--}}

<div class="patrocinadores">
    @foreach ($patrocinadores as $patrocinador)
        @php
            $logo = \App\Support\Arquivos::logoDoPatrocinador($patrocinador);
        @endphp

        @if ($patrocinador->site_url)
            {{-- rel="noopener": sem isso a página aberta ganha acesso a esta
                 pela window.opener. --}}
            <a class="patrocinador" href="{{ $patrocinador->site_url }}"
               target="_blank" rel="noopener"
               title="{{ $patrocinador->name }}">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $patrocinador->name }}" loading="lazy">
                @else
                    <span class="patrocinador__nome">{{ $patrocinador->name }}</span>
                @endif
            </a>
        @else
            <div class="patrocinador">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $patrocinador->name }}" loading="lazy">
                @else
                    <span class="patrocinador__nome">{{ $patrocinador->name }}</span>
                @endif
            </div>
        @endif
    @endforeach
</div>
