@extends('layouts.app')

@section('content')
<div class="py-12 bg-zinc-950 min-h-screen text-zinc-100 font-sans">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center border-b border-zinc-800 pb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-amber-500 tracking-wider">PESANAN SAYA</h1>
                <p class="text-sm text-zinc-400 mt-1">Daftar transaksi sewa alat media Manake Anda.</p>
            </div>
            <a href="{{ route('catalog') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-zinc-950 text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-amber-400 transition-all duration-300">
                Sewa Alat Baru
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg text-sm flex items-center shadow-lg">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg text-sm flex items-center shadow-lg">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Order List Grid -->
        @if($orders->isEmpty())
            <div class="text-center py-16 bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl">
                <svg class="mx-auto h-16 w-16 text-zinc-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <h3 class="text-lg font-bold text-zinc-300">Belum Ada Pesanan</h3>
                <p class="text-zinc-500 mt-2 max-w-sm mx-auto text-sm">Anda belum melakukan penyewaan alat media apapun. Silakan kunjungi katalog kami untuk memesan.</p>
                <a href="{{ route('catalog') }}" class="mt-6 inline-flex items-center px-5 py-2.5 bg-amber-500 text-zinc-950 text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-amber-400 transition-all duration-300 shadow-lg shadow-amber-500/10">
                    Lihat Katalog
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-zinc-900 border border-zinc-800 hover:border-amber-500/30 rounded-xl p-6 shadow-xl transition-all duration-300 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        
                        <!-- Order Identification -->
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-amber-500 font-mono font-bold text-base tracking-wider">{{ $order->order_number }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider 
                                    @if($order->payment_status === 'paid') bg-green-500/10 border border-green-500/30 text-green-400 
                                    @elseif($order->payment_status === 'pending') bg-yellow-500/10 border border-yellow-500/30 text-yellow-400
                                    @elseif($order->payment_status === 'expired') bg-red-500/10 border border-red-500/30 text-red-400
                                    @else bg-zinc-800 border border-zinc-700 text-zinc-400 @endif">
                                    {{ $order->payment_status === 'paid' ? 'Lunas' : ($order->payment_status === 'pending' ? 'Menunggu Pembayaran' : ($order->payment_status === 'expired' ? 'Kedaluwarsa' : 'Gagal')) }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-zinc-800 border border-zinc-700 text-zinc-300">
                                    @switch($order->rental_status)
                                        @case('waiting_payment') Menunggu Pembayaran @break
                                        @case('paid') Dibayar (Menunggu Pickup) @break
                                        @case('processed') Diproses @break
                                        @case('picked_up') Aktif Disewa @break
                                        @case('returned') Sudah Dikembalikan @break
                                        @case('damaged') Rusak @break
                                        @case('lost') Hilang @break
                                        @case('completed') Selesai @break
                                        @case('cancelled') Dibatalkan @break
                                        @case('expired') Kedaluwarsa @break
                                        @default {{ $order->rental_status }}
                                    @endswitch
                                </span>
                            </div>
                            
                            <div class="text-sm text-zinc-400 flex flex-wrap items-center gap-x-4 gap-y-1">
                                <div>
                                    <span class="text-zinc-500">Mulai:</span>
                                    <span class="font-medium text-zinc-300">{{ $order->rental_start_date->format('d M Y') }}</span>
                                </div>
                                <div class="hidden sm:block text-zinc-700">|</div>
                                <div>
                                    <span class="text-zinc-500">Selesai:</span>
                                    <span class="font-medium text-zinc-300">{{ $order->rental_end_date->format('d M Y') }}</span>
                                </div>
                                <div class="hidden sm:block text-zinc-700">|</div>
                                <div>
                                    <span class="font-bold text-amber-500/90">{{ $order->duration_days }} Hari</span>
                                </div>
                            </div>

                            <div class="text-xs text-zinc-500">
                                Total Alat: {{ $order->items->sum('qty') }} item
                            </div>
                        </div>

                        <!-- Cost and Actions -->
                        <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center border-t md:border-t-0 border-zinc-800 pt-4 md:pt-0 gap-4">
                            <div>
                                <div class="text-xs text-zinc-500">Total Biaya</div>
                                <div class="text-lg font-extrabold text-zinc-100">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 rounded-lg text-xs font-bold uppercase tracking-wider transition-all duration-300">
                                    Detail
                                </a>
                                @if($order->isPaid())
                                    <a href="{{ route('orders.invoice.download', $order->id) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 rounded-lg text-xs font-bold uppercase tracking-wider transition-all duration-300 shadow-md">
                                        Unduh PDF
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
