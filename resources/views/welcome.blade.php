@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Hero Section -->
    <section class="py-16 sm:py-24 text-center">
        <h1 class="text-4xl sm:text-6xl font-extrabold text-zinc-100 tracking-tight leading-none mb-6">
            Sewa Alat Produksi Media <br class="hidden sm:inline" />
            <span class="text-amber-500">Lebih Cepat dan Terstruktur</span>
        </h1>
        <p class="max-w-2xl mx-auto text-base sm:text-lg text-zinc-400 mb-8 font-light">
            Manake V2 mengoptimalkan proses bisnis sewa kamera, audio, dan alat produksi media profesional. Cepat, transparan, dan terintegrasi dengan Payment Gateway.
        </p>
        <div class="flex justify-center gap-4">
            <a href="{{ route('catalog') }}" class="bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold px-6 py-3 rounded-sm text-sm transition duration-150">
                Lihat Katalog
            </a>
            @guest
                <a href="{{ route('register') }}" class="border border-zinc-800 hover:border-amber-500/40 text-zinc-300 px-6 py-3 rounded-sm text-sm transition duration-150">
                    Mulai Daftar
                </a>
            @endguest
        </div>
    </section>

    <!-- Category Chips Segment -->
    <section class="py-8 border-t border-zinc-900">
        <h2 class="text-sm font-semibold text-zinc-500 uppercase tracking-widest text-center mb-6">
            Kategori Alat Produksi
        </h2>
        <div class="flex flex-wrap justify-center gap-2 max-w-4xl mx-auto">
            @foreach($categories as $category)
                <a href="{{ route('catalog', ['category' => $category->slug]) }}" class="px-4 py-2 rounded-sm bg-zinc-900/60 border border-zinc-850 hover:border-amber-500/30 hover:text-amber-400 text-sm transition duration-150">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </section>

    <!-- Featured Products Segment -->
    <section class="py-12 border-t border-zinc-900 mt-12">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-2xl font-bold text-zinc-100">Alat Populer Tersedia</h2>
                <p class="text-xs text-zinc-500 font-light mt-1">Alat produksi berkualitas yang siap mendukung proyek visual Anda.</p>
            </div>
            <a href="{{ route('catalog') }}" class="text-sm text-amber-500 hover:text-amber-400 underline font-medium">Lihat Semua &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($featured as $item)
                <div class="bg-zinc-900 border border-zinc-800/80 rounded-sm overflow-hidden flex flex-col justify-between hover:border-amber-500/20 transition duration-150 group">
                    <div class="p-6">
                        <div class="flex justify-between items-start gap-2 mb-3">
                            <span class="text-[10px] uppercase font-bold text-amber-500 tracking-wider">
                                {{ $item->category->name ?? 'Peralatan' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                                Tersedia
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-zinc-100 group-hover:text-amber-500 transition duration-150 mb-2">
                            {{ $item->name }}
                        </h3>
                        <p class="text-xs text-zinc-400 font-light line-clamp-2 mb-4">
                            {{ $item->description }}
                        </p>
                    </div>

                    <div class="p-6 bg-zinc-900/60 border-t border-zinc-850 flex items-center justify-between">
                        <div>
                            <span class="text-zinc-500 text-[10px] block uppercase tracking-wider font-light">Tarif / Hari</span>
                            <span class="text-sm font-extrabold text-amber-500 font-mono">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('product.show', $item->slug) }}" class="bg-zinc-800 hover:bg-amber-500 hover:text-zinc-950 text-zinc-300 font-bold px-4 py-2 rounded-sm text-xs transition duration-150">
                            Cek Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-12 text-center text-zinc-500 text-sm">
                    Belum ada peralatan sewa siap pakai yang disematkan.
                </div>
            @endforelse
        </div>
    </section>

    <!-- How it works Segment -->
    <section class="py-12 border-t border-zinc-900 mt-12 bg-zinc-900/30 rounded-sm p-8 border border-zinc-900/60">
        <h2 class="text-2xl font-bold text-center text-zinc-100 mb-8">Cara Kerja Penyewaan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 text-center">
            <div class="p-4">
                <span class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-500 font-bold flex items-center justify-center mx-auto mb-4 text-base">1</span>
                <h3 class="font-bold text-zinc-200 text-sm mb-1">Pilih Alat</h3>
                <p class="text-xs text-zinc-400 font-light">Temukan alat produksi media di katalog premium kami.</p>
            </div>
            <div class="p-4">
                <span class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-500 font-bold flex items-center justify-center mx-auto mb-4 text-base">2</span>
                <h3 class="font-bold text-zinc-200 text-sm mb-1">Cek Tanggal</h3>
                <p class="text-xs text-zinc-400 font-light">Validasi ketersediaan unit beserta 1-hari buffer operasional.</p>
            </div>
            <div class="p-4">
                <span class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-500 font-bold flex items-center justify-center mx-auto mb-4 text-base">3</span>
                <h3 class="font-bold text-zinc-200 text-sm mb-1">Masuk Keranjang</h3>
                <p class="text-xs text-zinc-400 font-light">Persiapkan daftar sewa dengan kalkulasi tarif otomatis.</p>
            </div>
            <div class="p-4">
                <span class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-500 font-bold flex items-center justify-center mx-auto mb-4 text-base">4</span>
                <h3 class="font-bold text-zinc-200 text-sm mb-1">Checkout & Bayar</h3>
                <p class="text-xs text-zinc-400 font-light">Selesaikan checkout & bayar instan via Payment Gateway.</p>
            </div>
        </div>
        <div class="mt-8 text-center bg-zinc-950/40 p-4 rounded-sm border border-zinc-850 max-w-2xl mx-auto">
            <p class="text-xs text-zinc-500 font-light">
                <strong class="text-amber-500 font-bold">Catatan Keamanan:</strong> Login hanya diperlukan saat Anda ingin menambahkan alat ke dalam keranjang atau memproses checkout preview. Publik bebas menjelajah katalog.
            </p>
        </div>
    </section>
</div>
@endsection
