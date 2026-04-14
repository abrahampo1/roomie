@props(['title' => 'Editor'])

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1a1a2e">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — Roomie</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/LogoRoomie_Estrella.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|fredoka:500,600,700|jetbrains-mono:400,500" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-cream text-navy antialiased selection:bg-navy selection:text-cream overflow-hidden">

    <svg width="0" height="0" class="absolute">
        <defs>
            <symbol id="roomie-sparkle" viewBox="0 0 24 24">
                <path d="M12 0 C12 5.5 6.5 12 0 12 C6.5 12 12 18.5 12 24 C12 18.5 17.5 12 24 12 C17.5 12 12 5.5 12 0 Z" fill="currentColor"/>
            </symbol>
        </defs>
    </svg>

    {{ $slot }}

</body>
</html>
