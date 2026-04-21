@props(['event'])

<a class="event-card" href="{{ url('/event/' . $event->id) }}">
    <div class="event-card__image-wrapper">
        @php
            $imageRelativePath = 'images/events/' . $event->id . '/card-' . $event->banner_url;
            $hasImage = $event->banner_url && file_exists(public_path($imageRelativePath));
        @endphp

        @if($hasImage)
            <img src="{{ asset($imageRelativePath) }}" class="event-card__image" alt="{{ $event->title }}">
        @else
            <img src="{{ asset('images/default/card.jpg') }}" class="event-card__image" alt="{{ $event->title }}">
            <div class="default-card-overlay">
                <h3 class="event-card-overlay-title">{{ $event->title }}</h3>
            </div>
        @endif

        @php
            $shareMessage = "Confira este evento: *" . $event->title . "*\n📍 " . $event->location . "\n\n" . url('/event/' . $event->id);
            $whatsappUrl = "https://api.whatsapp.com/send?text=" . urlencode($shareMessage);
        @endphp
        <div class="event-card__share" onclick="event.preventDefault(); window.open('{{ $whatsappUrl }}', '_blank');" title="Compartilhar no WhatsApp">
            <i data-feather="share-2"></i>
        </div>
    </div>
    <div class="event-card__date">
        <span class="event-card__date-day">{{ \Carbon\Carbon::parse($event->event_date)->format('d') }}</span>
        <span class="event-card__date-month">{{ strtoupper(\Carbon\Carbon::parse($event->event_date)->translatedFormat('M')) }}</span>
    </div>

    <div class="event-card__content">
        <span class="event-card__category">Corrida de Rua</span>
        <h2 class="event-card__title">{{ $event->title }}</h2>

        <div class="event-card__footer">
        <div class="event-card__location">📍 {{ $event->location }}</div>
        <div class="event-card__distances">
            @if(isset($event->modalities) && $event->modalities->isNotEmpty())
                @foreach($event->modalities as $modality)
                    {{ $modality->name }}
                @endforeach
            @else
                Distâncias a definir
            @endif
        </div>
        </div>
    </div>
</a>
