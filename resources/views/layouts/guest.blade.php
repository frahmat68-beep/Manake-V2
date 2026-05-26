<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Manake V2') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-zinc-950 text-zinc-100 selection:bg-amber-500 selection:text-zinc-950">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-zinc-950 px-4">
            <div class="mb-6 text-center">
                <a href="/" class="flex flex-col items-center gap-1">
                    <span class="text-amber-500 font-extrabold text-4xl tracking-widest font-mono">MANAKE</span>
                    <span class="text-xs text-zinc-500 font-medium">SISTEM PENYEWAAN ALAT MEDIA</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-8 bg-zinc-900 border border-zinc-800/80 shadow-2xl overflow-hidden rounded-sm">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
