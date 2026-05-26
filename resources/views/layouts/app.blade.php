<?php
// Base layout for Manake V2
?>
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
        <div class="min-h-screen flex flex-col justify-between">
            <div>
                @include('layouts.navigation')

                <!-- Flash Success Notification -->
                @if (session('success'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-sm flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Flash Error Notification -->
                @if ($errors->any())
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                        <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-sm text-sm">
                            <div class="flex items-center gap-2 font-semibold mb-1">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>Terjadi Kesalahan:</span>
                            </div>
                            <ul class="list-disc list-inside space-y-0.5 text-xs text-red-400/80">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                <main class="py-6">
                    {{ $slot ?? '' }}
                    @yield('content')
                </main>
            </div>

            <!-- Footer -->
            <footer class="bg-zinc-900 border-t border-zinc-800/80 py-8 mt-12 text-zinc-500 text-xs">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 font-extrabold tracking-widest font-mono text-base">MANAKE</span>
                        <span>&copy; {{ date('Y') }} Sistem Penyewaan Alat Media. All Rights Reserved.</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-zinc-600 font-medium">Proposal Thesis: Business Process Optimization</span>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
