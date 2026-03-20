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
        overflow: hidden;
    }

    .main-banner__image {
        width: 100%;
        height: auto;
        display: block;
    }
</style>

<div class="main-banner-container">
    <div class="main-banner">
        <img src="{{ asset('img/banner.jpg') }}" class="main-banner__image" alt="Banner Principal">
    </div>

</div>
