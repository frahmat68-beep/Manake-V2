@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-zinc-100 tracking-tight">Keranjang Belanja</h1>
        <p class="text-xs text-zinc-500 font-light mt-1">Kelola daftar sewa peralatan media Anda sebelum memproses checkout.</p>
    </div>

    @if($summary['items']->isEmpty())
        <div class="py-24 text-center bg-zinc-900/20 border border-zinc-900 rounded-sm">
            <svg class="w-16 h-16 text-zinc-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h2 class="text-zinc-300 font-bold text-base mb-1">Keranjang Belanja Kosong</h2>
            <p class="text-xs text-zinc-500 font-light mb-6">Pilih peralatan media terlebih dahulu dari katalog kami.</p>
            <a href="{{ route('catalog') }}" class="bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold px-6 py-2.5 rounded-sm text-sm transition duration-150">
                Lihat Katalog Alat
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Left: Cart Items List -->
            <div class="col-span-1 lg:col-span-2 space-y-4">
                @foreach($summary['items'] as $item)
                    <div class="bg-zinc-900 border border-zinc-800 rounded-sm p-6 flex flex-col sm:flex-row justify-between sm:items-center gap-6">
                        <!-- Item Details -->
                        <div class="space-y-2">
                            <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block">
                                {{ $item->equipment->category->name ?? 'Peralatan' }}
                            </span>
                            <h3 class="text-base font-bold text-zinc-100">
                                <a href="{{ route('product.show', $item->equipment->slug) }}" class="hover:text-amber-500 transition duration-150">
                                    {{ $item->equipment->name }}
                                </a>
                            </h3>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 font-light">
                                <span>Tanggal: <strong class="text-zinc-400 font-semibold">{{ $item->rental_start_date->format('d M Y') }} - {{ $item->rental_end_date->format('d M Y') }}</strong></span>
                                <span>Durasi: <strong class="text-zinc-400 font-semibold">{{ $item->duration_days }} hari</strong></span>
                            </div>
                        </div>

                        <!-- Quantity updates and remove controls -->
                        <div class="flex items-center gap-4 justify-between sm:justify-end shrink-0">
                            <!-- Update Quantity Form -->
                            <form method="POST" action="{{ route('cart.update', $item->id) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input 
                                    type="number" 
                                    name="qty" 
                                    value="{{ $item->qty }}" 
                                    min="1"
                                    max="{{ $item->equipment->stock }}"
                                    class="w-16 bg-zinc-950 border border-zinc-800 rounded-sm text-zinc-100 text-xs text-center py-1.5 focus:border-amber-500/50 focus:ring-amber-500/20"
                                />
                                <button type="submit" class="bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold px-2.5 py-1.5 rounded-sm text-xs transition duration-150 shrink-0">
                                    Update
                                </button>
                            </form>

                            <!-- Remove Form -->
                            <form method="POST" action="{{ route('cart.destroy', $item->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-zinc-500 hover:text-red-400 p-2 transition duration-150" title="Hapus dari keranjang">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <!-- Calculations and price snapshot -->
                        <div class="text-right border-t border-zinc-850 pt-4 sm:border-0 sm:pt-0 shrink-0 flex sm:flex-col justify-between sm:justify-start items-center sm:items-end">
                            <span class="text-zinc-500 text-[10px] sm:block uppercase tracking-wider font-light mb-0.5">Subtotal Item</span>
                            <div class="text-right">
                                <span class="text-sm font-extrabold text-amber-500 font-mono">Rp {{ number_format($item->item_subtotal, 0, ',', '.') }}</span>
                                <span class="text-zinc-500 text-[9px] block font-light">({{ $item->qty }} unit &times; Rp {{ number_format($item->price_per_day, 0, ',', '.') }})</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right: Cost Summary breakdown -->
            <div class="col-span-1">
                <div class="bg-zinc-900 border border-zinc-800 rounded-sm p-6 space-y-4">
                    <h2 class="text-base font-bold text-zinc-100 border-b border-zinc-850 pb-2">Ringkasan Biaya</h2>
                    
                    <div class="space-y-2 text-xs font-light">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Subtotal Sewa</span>
                            <span class="text-zinc-300 font-mono">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">PPN (11%)</span>
                            <span class="text-zinc-300 font-mono">Rp {{ number_format($summary['tax_amount'], 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-zinc-850 pt-3 flex justify-between items-baseline">
                            <span class="text-zinc-400 font-semibold">Total Biaya</span>
                            <span class="text-lg font-extrabold text-amber-500 font-mono">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-zinc-850">
                        <a href="{{ route('checkout.index') }}" class="block w-full text-center bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold py-3 rounded-sm text-sm transition duration-150">
                            Lanjut ke Preview Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
