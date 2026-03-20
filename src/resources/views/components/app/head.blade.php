<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />

    {{-- <link href="css/styles.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{ asset('css/app/top-bar.css') }}">

    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />

    {{-- <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script> --}}
    <script src="{{ asset('js/app/font-awesome.js') }}"></script>

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.24.1/feather.min.js" crossorigin="anonymous"></script> --}}
    <script src="{{ asset('js/app/feather-icons.js') }}"></script>

    <meta charset="UTF-8">

    <title>@yield('title', 'Corre Virtual')</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">

    @stack('styles')
</head>
