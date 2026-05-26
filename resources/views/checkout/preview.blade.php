@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-zinc-100 tracking-tight">Preview Checkout</h1>
        <p class="text-xs text-zinc-500 font-light mt-1">Halaman peninjauan pesanan sebelum melakukan pembayaran online via Midtrans.</p>
    </div>

    <!-- Alert Box: Profile Complete status -->
    @if($profileIncomplete)
        <div class="mb-8 bg-amber-500/10 border border-amber-500/30 text-amber-400 p-4 rounded-sm text-xs font-light space-y-1">
            <div class="flex items-center gap-2 font-bold mb-1">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>Informasi Profil Pengguna Belum Lengkap</span>
            </div>
            <p>
                Akun Anda belum dilengkapi dengan data profil penting (Nomor HP, NIK, atau Alamat Rumah masih kosong). 
                Meskipun Anda dapat melihat preview ini, Anda akan diminta melengkapi data identitas di halaman profil saat melakukan transaksi nyata demi keabsahan peminjaman barang.
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Left: Staged Order items list -->
        <div class="col-span-1 lg:col-span-2 space-y-4">
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm p-6">
                <h2 class="text-base font-bold text-zinc-100 border-b border-zinc-850 pb-2.5 mb-4">Daftar Peralatan yang Disewa</h2>
                <div class="divide-y divide-zinc-850">
                    @foreach($preview['items'] as $item)
                        <div class="py-4 first:pt-0 last:pb-0 flex justify-between items-start gap-4 text-sm font-light">
                            <div>
                                <h3 class="font-bold text-zinc-200">{{ $item->equipment->name }}</h3>
                                <p class="text-xs text-zinc-500 mt-1">
                                    {{ $item->rental_start_date->format('d M Y') }} - {{ $item->rental_end_date->format('d M Y') }} 
                                    <span class="text-zinc-600">({{ $item->duration_days }} hari)</span>
                                </p>
                                <span class="text-xs text-zinc-500 mt-0.5 block">Jumlah: {{ $item->qty }} unit &times; Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</span>
                            </div>
                            <span class="font-bold text-amber-500 font-mono shrink-0">
                                Rp {{ number_format($item->item_subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Grand Totals & Payment placeholder -->
        <div class="col-span-1">
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm p-6 space-y-6">
                <h2 class="text-base font-bold text-zinc-100 border-b border-zinc-850 pb-2">Rincian Total Biaya</h2>
                
                <div class="space-y-2 text-xs font-light">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Subtotal Sewa</span>
                        <span class="text-zinc-300 font-mono">Rp {{ number_format($preview['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">PPN (11%)</span>
                        <span class="text-zinc-300 font-mono">Rp {{ number_format($preview['tax_amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-zinc-850 pt-3 flex justify-between items-baseline">
                        <span class="text-zinc-400 font-semibold">Total Pembayaran</span>
                        <span class="text-lg font-extrabold text-amber-500 font-mono">Rp {{ number_format($preview['total_amount'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-850 space-y-3">
                    <button 
                        type="button" 
                        disabled
                        class="w-full bg-zinc-850 text-zinc-600 font-bold py-3 rounded-sm text-sm cursor-not-allowed border border-zinc-800"
                    >
                        Lanjut Pembayaran (Fase 5)
                    </button>
                    <div class="bg-zinc-950/40 p-3 rounded-sm border border-zinc-850 text-[10px] leading-relaxed text-zinc-500 font-light">
                        <strong class="text-amber-500/80 font-bold block mb-1">Catatan Integrasi:</strong>
                        Pembayaran terintegrasi dengan Midtrans Snap sandbox serta pencatatan transaksi final di database akan diaktifkan sepenuhnya pada fase berikutnya (Phase 5).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
