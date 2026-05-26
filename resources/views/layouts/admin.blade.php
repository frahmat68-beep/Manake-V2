<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Manake Admin Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,550,700,900" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-zinc-100 font-sans antialiased min-h-screen">
    
    <div class="flex min-h-screen flex-col md:flex-row">
        
        <!-- Sidebar -->
        <aside class="w-full md:w-64 bg-zinc-900 border-b md:border-b-0 md:border-r border-zinc-800 flex-shrink-0 flex flex-col">
            <div class="p-6 border-b border-zinc-800/80 flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="text-amber-500 font-black tracking-widest font-mono text-xl">MANAKE</span>
                    <span class="text-[10px] font-bold tracking-wider px-2 py-0.5 bg-amber-500/10 text-amber-400 rounded-full border border-amber-500/20">ADMIN</span>
                </a>
            </div>

            <!-- Navigation Lists -->
            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300 {{ request()->routeIs('admin.categories.*') ? 'bg-amber-500 text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Kategori
                </a>

                <a href="{{ route('admin.equipments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300 {{ request()->routeIs('admin.equipments.*') ? 'bg-amber-500 text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                    Peralatan
                </a>

                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300 {{ request()->routeIs('admin.orders.*') ? 'bg-amber-500 text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Pesanan
                </a>

                <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold uppercase tracking-wider transition-all duration-300 {{ request()->routeIs('admin.payments.*') ? 'bg-amber-500 text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Pembayaran
                </a>
            </nav>

            <!-- Bottom utility -->
            <div class="p-4 border-t border-zinc-800/80 space-y-2">
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 w-full py-2.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-bold uppercase tracking-widest rounded-lg border border-zinc-700 transition-all duration-300">
                    Kembali Ke Website
                </a>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="flex items-center justify-center gap-2 w-full py-2.5 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white text-xs font-bold uppercase tracking-widest rounded-lg border border-red-500/20 transition-all duration-300">
                        Keluar (Logout)
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-6 md:p-10 overflow-y-auto">
            @yield('content')
        </main>

    </div>

</body>
</html>
