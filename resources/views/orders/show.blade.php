@extends('layouts.app')

@section('content')
<div class="py-12 bg-zinc-950 min-h-screen text-zinc-100 font-sans">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Navigation Breadcrumbs -->
        <div class="mb-6">
            <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm text-zinc-400 hover:text-amber-500 transition-colors duration-300">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar Pesanan
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

        <!-- Main Dashboard Card -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl overflow-hidden mb-8">
            
            <!-- Header Block -->
            <div class="bg-gradient-to-r from-zinc-900 to-zinc-900 border-b border-zinc-800 p-6 sm:p-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="space-y-1">
                    <div class="text-xs font-bold uppercase tracking-widest text-zinc-500">KODE RESERVASI</div>
                    <h2 class="text-2xl font-black text-amber-500 font-mono tracking-wider">{{ $order->order_number }}</h2>
                    <p class="text-xs text-zinc-500">Dibuat pada {{ $order->created_at->format('d M Y H:i') }} WIB</p>
                </div>
                <div class="flex flex-col sm:items-end gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider text-center
                        @if($order->payment_status === 'paid') bg-green-500/10 border border-green-500/30 text-green-400 
                        @elseif($order->payment_status === 'pending') bg-yellow-500/10 border border-yellow-500/30 text-yellow-400
                        @elseif($order->payment_status === 'expired') bg-red-500/10 border border-red-500/30 text-red-400
                        @else bg-zinc-800 border border-zinc-700 text-zinc-400 @endif">
                        PEMBAYARAN: {{ $order->payment_status === 'paid' ? 'LUNAS' : ($order->payment_status === 'pending' ? 'MENUNGGU' : ($order->payment_status === 'expired' ? 'KEDALUWARSA' : 'GAGAL')) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-zinc-800 border border-zinc-700 text-zinc-300 text-center">
                        STATUS SEWA: 
                        @switch($order->rental_status)
                            @case('waiting_payment') Menunggu Pembayaran @break
                            @case('paid') Lunas (Siap Pickup) @break
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
            </div>

            <!-- Content Body -->
            <div class="p-6 sm:p-8 space-y-8">
                
                <!-- Date Details Box -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-zinc-950 p-5 border border-zinc-800/80 rounded-xl">
                    <div class="space-y-1 border-b sm:border-b-0 sm:border-r border-zinc-800/80 pb-4 sm:pb-0">
                        <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">MULAI RESERVASI</span>
                        <div class="text-base font-bold text-zinc-200">{{ $order->rental_start_date->format('d M Y') }}</div>
                        <p class="text-[11px] text-zinc-500">Mulai pickup jam 09:00 WIB</p>
                    </div>
                    <div class="space-y-1 sm:pl-4 border-b sm:border-b-0 sm:border-r border-zinc-800/80 pb-4 sm:pb-0">
                        <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">SELESAI RESERVASI</span>
                        <div class="text-base font-bold text-zinc-200">{{ $order->rental_end_date->format('d M Y') }}</div>
                        <p class="text-[11px] text-zinc-500">Maks. dikembalikan jam 18:00 WIB</p>
                    </div>
                    <div class="space-y-1 sm:pl-4 flex flex-col justify-center">
                        <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">DURASI PENYETELAN</span>
                        <div class="text-2xl font-black text-amber-500">{{ $order->duration_days }} Hari</div>
                        <p class="text-[10px] text-zinc-500">Inclusive (Operasional buffer +1 hari)</p>
                    </div>
                </div>

                <!-- Equipment Items Snapshot -->
                <div>
                    <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-4">ALAT YANG DISEWA</h3>
                    <div class="divide-y divide-zinc-800 border-y border-zinc-800">
                        @foreach($order->items as $item)
                            <div class="py-4 flex justify-between items-center gap-4">
                                <div class="space-y-1">
                                    <div class="font-bold text-zinc-200 text-sm hover:text-amber-500 transition-colors duration-300">
                                        {{ $item->equipment_name }}
                                    </div>
                                    <div class="text-xs text-zinc-500 flex items-center gap-4">
                                        <span>Jumlah: <strong class="text-zinc-300">{{ $item->qty }} unit</strong></span>
                                        <span>Tarif: <strong class="text-zinc-300">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}/hari</strong></span>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-zinc-200 font-mono">
                                    Rp {{ number_format($item->item_subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Price Summary breakdown -->
                <div class="border-t border-zinc-800 pt-6 space-y-3 max-w-sm ml-auto">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-400">Subtotal Sewa</span>
                        <span class="font-medium text-zinc-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-400">PPN (11%)</span>
                        <span class="font-medium text-zinc-200">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    @if($order->additional_fee > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-400">Biaya Tambahan</span>
                            <span class="font-medium text-zinc-200">Rp {{ number_format($order->additional_fee, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-zinc-800 pt-3 text-base font-bold">
                        <span class="text-amber-500">TOTAL BIAYA</span>
                        <span class="text-zinc-100 font-mono">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Call-to-actions / Payment Instructions / Status details -->
                <div class="border-t border-zinc-800 pt-8 flex flex-col items-center justify-center space-y-4">
                    
                    @if($order->payment_status === 'pending')
                        
                        <div class="text-center space-y-2 max-w-md">
                            <p class="text-xs text-zinc-400">Selesaikan transaksi pembayaran sebelum batas waktu kedaluwarsa.</p>
                            <p class="text-xs font-bold text-amber-500/90 font-mono">Batas Pembayaran: {{ $order->expired_at->format('d M Y H:i') }} WIB</p>
                        </div>

                        <div class="flex flex-wrap gap-4 justify-center mt-2 w-full">
                            @if($order->payment && $order->payment->snap_token)
                                <button id="pay-button" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-amber-500 hover:bg-amber-400 text-zinc-950 text-sm font-black uppercase tracking-widest rounded-xl transition-all duration-300 shadow-lg shadow-amber-500/20 active:scale-95">
                                    Bayar Sekarang
                                </button>
                                @if($order->payment->snap_redirect_url)
                                    <a href="{{ $order->payment->snap_redirect_url }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 text-sm font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
                                        Metode Pembayaran Lain
                                    </a>
                                @endif
                            @else
                                <form action="{{ route('payments.refresh', $order->id) }}" method="POST" class="w-full sm:w-auto">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 text-sm font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
                                        Refresh Token Pembayaran
                                    </button>
                                </form>
                            @endif
                        </div>

                    @elseif($order->payment_status === 'paid')
                        
                        <div class="text-center space-y-4">
                            <div class="inline-flex p-3 bg-green-500/10 border border-green-500/20 text-green-400 rounded-full mb-2">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h4 class="text-lg font-bold text-green-400">Pembayaran Terverifikasi Lunas!</h4>
                            <p class="text-xs text-zinc-400 max-w-sm mx-auto">Silakan bawa Kode Reservasi atau tunjukkan Invoice PDF ke kantor operasional kami saat melakukan penyerahan alat.</p>
                            
                            <div class="flex flex-wrap gap-4 justify-center pt-2">
                                <a href="{{ route('orders.invoice', $order->id) }}" class="inline-flex items-center px-6 py-3 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
                                    Lihat Invoice
                                </a>
                                <a href="{{ route('orders.invoice.download', $order->id) }}" class="inline-flex items-center px-6 py-3 bg-amber-500 hover:bg-amber-400 text-zinc-950 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-300 shadow-md shadow-amber-500/10">
                                    Unduh Invoice PDF
                                </a>
                            </div>
                        </div>

                    @elseif($order->payment_status === 'expired')
                        
                        <div class="text-center space-y-2 bg-red-500/5 border border-red-500/10 rounded-xl p-5 max-w-md w-full">
                            <h4 class="text-sm font-bold text-red-400">Transaksi Kedaluwarsa</h4>
                            <p class="text-xs text-zinc-500">Batas pembayaran terlampaui. Penyewaan dibatalkan secara otomatis karena alat harus dilepas ke antrean publik.</p>
                            <a href="{{ route('catalog') }}" class="inline-flex items-center text-xs font-bold text-amber-500 hover:underline mt-2">
                                Cari Alat Lain di Katalog &rarr;
                            </a>
                        </div>

                    @else
                        
                        <div class="text-center space-y-2 bg-zinc-950 border border-zinc-800 rounded-xl p-5 max-w-md w-full">
                            <h4 class="text-sm font-bold text-zinc-400">Transaksi Dibatalkan / Gagal</h4>
                            <p class="text-xs text-zinc-500">Pembayaran gagal diselesaikan atau ditolak oleh gerbang pembayaran.</p>
                        </div>

                    @endif

                </div>

            </div>

        </div>

        <!-- Rental Guidelines Alert -->
        <div class="bg-zinc-900/60 border border-zinc-850 p-6 rounded-xl space-y-3">
            <h4 class="text-sm font-bold text-amber-500/90 tracking-wide flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Ketentuan Pengambilan Alat
            </h4>
            <ul class="list-disc list-inside text-xs text-zinc-400 space-y-2">
                <li>Pengambilan alat hanya bisa dilakukan jika status pembayaran sudah **LUNAS**.</li>
                <li>Membawa Kartu Identitas Fisik (KTP/KTM/SIM) asli yang sesuai dengan profil akun Anda.</li>
                <li>Setiap alat yang disewa memiliki masa toleransi buffer 1 hari operasional dari kami untuk proses QC (Quality Control) sebelum dan sesudah disewa.</li>
            </ul>
        </div>

    </div>
</div>

@if($order->payment_status === 'pending' && $order->payment && $order->payment->snap_token)
    <!-- Midtrans Snap overlay script -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script type="text/javascript">
        const payButton = document.getElementById('pay-button');
        if (payButton) {
            payButton.onclick = function() {
                snap.pay('{{ $order->payment->snap_token }}', {
                    onSuccess: function(result) {
                        window.location.reload();
                    },
                    onPending: function(result) {
                        window.location.reload();
                    },
                    onError: function(result) {
                        window.location.reload();
                    },
                    onClose: function() {
                        // User closed the popup, do nothing
                    }
                });
            };
        }
    </script>
@endif
@endsection
