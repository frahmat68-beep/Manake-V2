@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-zinc-100 tracking-tight">Katalog Peralatan Media</h1>
        <p class="text-xs text-zinc-500 font-light mt-1">Gunakan kotak pencarian atau pilih kategori untuk menyaring daftar alat produksi.</p>
    </div>

    <!-- Filter & Search Controls -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start mb-8">
        <!-- Search bar -->
        <form method="GET" action="{{ route('catalog') }}" class="col-span-1 lg:col-span-3 flex gap-2">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <div class="relative flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari alat berdasarkan nama..." 
                    class="w-full bg-zinc-900 border border-zinc-800 rounded-sm text-zinc-100 placeholder-zinc-500 text-sm focus:border-amber-500/50 focus:ring-amber-500/20 py-2 px-4 transition duration-150"
                />
            </div>
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold px-5 py-2 rounded-sm text-sm transition duration-150 shrink-0">
                Cari
            </button>
            @if(request('search') || request('category'))
                <a href="{{ route('catalog') }}" class="bg-zinc-900 hover:bg-zinc-800 border border-zinc-850 text-zinc-400 px-4 py-2 rounded-sm text-sm transition duration-150 flex items-center">
                    Reset
                </a>
            @endif
        </form>

        <!-- Active Category -->
        <div class="col-span-1 flex justify-end">
            <span class="text-xs text-zinc-500 bg-zinc-900/60 border border-zinc-850 px-3 py-2 rounded-sm w-full lg:w-auto text-center lg:text-left">
                Menampilkan <strong class="text-zinc-200 font-semibold">{{ $equipments->total() }}</strong> alat
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Category Filter list -->
        <div class="col-span-1 bg-zinc-900/40 border border-zinc-900 rounded-sm p-5">
            <h2 class="text-sm font-semibold text-zinc-300 uppercase tracking-wider mb-4">Saring Kategori</h2>
            <div class="flex flex-col gap-1">
                <a 
                    href="{{ route('catalog', request()->only('search')) }}" 
                    class="px-3 py-2 rounded-sm text-sm transition duration-150 {{ !request('category') ? 'bg-amber-500/10 text-amber-400 font-semibold border-l-2 border-amber-500' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}"
                >
                    Semua Kategori
                </a>
                @foreach($categories as $cat)
                    <a 
                        href="{{ route('catalog', array_merge(request()->only('search'), ['category' => $cat->slug])) }}" 
                        class="px-3 py-2 rounded-sm text-sm transition duration-150 {{ request('category') === $cat->slug ? 'bg-amber-500/10 text-amber-400 font-semibold border-l-2 border-amber-500' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}"
                    >
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Product Grid listing -->
        <div class="col-span-1 lg:col-span-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                @forelse($equipments as $item)
                    <div class="bg-zinc-900 border border-zinc-800/80 rounded-sm overflow-hidden flex flex-col justify-between hover:border-amber-500/20 transition duration-150 group">
                        <div class="p-5">
                            <div class="flex justify-between items-start gap-2 mb-3">
                                <span class="text-[10px] uppercase font-bold text-amber-500 tracking-wider">
                                    {{ $item->category->name ?? 'Peralatan' }}
                                </span>
                                
                                @if($item->status === \App\Models\Equipment::STATUS_READY)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                                        Tersedia
                                    </span>
                                @elseif($item->status === \App\Models\Equipment::STATUS_MAINTENANCE)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/25">
                                        Perbaikan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/25">
                                        Tidak Siap
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-zinc-100 group-hover:text-amber-500 transition duration-150 mb-2">
                                {{ $item->name }}
                            </h3>
                            <p class="text-xs text-zinc-400 font-light line-clamp-2 mb-4">
                                {{ $item->description }}
                            </p>
                            <div class="text-[10px] text-zinc-500 font-light flex items-center gap-1">
                                <span>Stok Gudang:</span>
                                <strong class="text-zinc-300 font-semibold">{{ $item->stock }} unit</strong>
                            </div>
                        </div>

                        <div class="p-5 bg-zinc-900/60 border-t border-zinc-850 flex items-center justify-between">
                            <div>
                                <span class="text-zinc-500 text-[10px] block uppercase tracking-wider font-light">Tarif / Hari</span>
                                <span class="text-sm font-extrabold text-amber-500 font-mono">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</span>
                            </div>
                            <a href="{{ route('product.show', $item->slug) }}" class="bg-zinc-800 hover:bg-amber-500 hover:text-zinc-950 text-zinc-300 font-bold px-3 py-1.5 rounded-sm text-xs transition duration-150">
                                Cek Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-24 text-center bg-zinc-900/20 border border-zinc-900/50 rounded-sm">
                        <svg class="w-12 h-12 text-zinc-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-zinc-300 font-bold text-sm mb-1">Alat tidak ditemukan</h3>
                        <p class="text-xs text-zinc-500 font-light">Silakan ganti kata kunci pencarian Anda atau pilih kategori lainnya.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination links -->
            <div class="mt-8">
                {{ $equipments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
