@props(['event'])

<a class="event-card" href="{{ url('/event/' . $event->id) }}">
    <div class="event-card__image-wrapper">
        @php
            $bannerPath = $event->banner_url ? asset('images/events/' . $event->id . '/card-' . $event->banner_url) : asset('images/events/default-card.jpg');
        @endphp
        <img src="{{ $bannerPath }}" class="event-card__image" alt="{{ $event->title }}">
        <div class="event-card__share">🔗</div>
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
