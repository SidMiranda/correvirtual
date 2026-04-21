<style>
    .main-banner-container {
        position: relative;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .main-banner {
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .main-banner__image {
        width: 100%;
        height: auto;
        display: block;
    }

    .default-banner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 20px;
    }

    .organizer-banner-title {
        font-size: 3rem;
        font-weight: 800;
        color: #ffffff;
        text-transform: uppercase;
        text-shadow: 0 0 15px rgba(0, 0, 0, 0.8), 0 0 30px rgba(0, 0, 0, 0.6);
        margin-bottom: 10px;
    }

    .organizer-banner-email {
        font-size: 1.2rem;
        color: #e0e0e0;
        font-weight: 500;
        text-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
    }

    @media (max-width: 768px) {
        .organizer-banner-title {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        .organizer-banner-email {
            font-size: 1rem;
        }
    }
</style>

<div class="main-banner-container">
    <div class="main-banner">
        @php
            $desktopBanner = 'images/organizers/'.$organizerId.'/banner.jpg';
            $mobileBanner = 'images/organizers/'.$organizerId.'/banner-mobile.jpg';
            $hasDesktop = file_exists(public_path($desktopBanner));
            $hasMobile = file_exists(public_path($mobileBanner));
            
            $defaultDesktop = 'images/default/banner.jpg';
            $defaultMobile = 'images/default/banner-mobile.jpg';
            $hasDefaultMobile = file_exists(public_path($defaultMobile));
        @endphp

        @if($hasDesktop || $hasMobile)
            <picture>
                @if($hasMobile)
                    <source media="(max-width: 1024px)" srcset="{{ asset($mobileBanner) }}">
                @endif
                <img src="{{ asset($hasDesktop ? $desktopBanner : $mobileBanner) }}" class="main-banner__image" alt="Banner Principal">
            </picture>
        @else
            <picture>
                @if($hasDefaultMobile)
                    <source media="(max-width: 1024px)" srcset="{{ asset($defaultMobile) }}">
                @endif
                <img src="{{ asset($defaultDesktop) }}" class="main-banner__image" alt="Banner Principal">
            </picture>
            <div class="default-banner-overlay">
                <h1 class="organizer-banner-title">{{ $organizerName }}</h1>
                <p class="organizer-banner-email">{{ $organizerEmail }}</p>
            </div>
        @endif
    </div>
</div>
