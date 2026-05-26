@extends('layouts.admin')

@section('content')
<div class="space-y-8 font-sans">
    
    <!-- Title -->
    <div>
        <h1 class="text-3xl font-black text-amber-500 tracking-wider uppercase">MONITORING PESANAN</h1>
        <p class="text-xs text-zinc-500 font-light mt-1">Daftar reservasi rental, verifikasi pickup fisik, pencatatan status pengembalian, dan denda.</p>
    </div>

    <!-- Filter & Search Board -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 shadow-xl">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <!-- Search -->
            <div class="space-y-2">
                <label for="search" class="text-[10px] font-bold text-zinc-550 uppercase tracking-widest block">Cari</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search"
                    value="{{ request('search') }}"
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-xs px-3 py-2 text-zinc-100 placeholder-zinc-700 outline-none"
                    placeholder="Kode / Email Customer"
                >
            </div>

            <!-- Payment status -->
            <div class="space-y-2">
                <label for="payment_status" class="text-[10px] font-bold text-zinc-550 uppercase tracking-widest block">Status Bayar</label>
                <select 
                    name="payment_status" 
                    id="payment_status"
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-xs px-3 py-2 text-zinc-100 outline-none"
                >
                    <option value="">Semua</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="expired" {{ request('payment_status') === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>

            <!-- Rental status -->
            <div class="space-y-2">
                <label for="rental_status" class="text-[10px] font-bold text-zinc-550 uppercase tracking-widest block">Status Sewa</label>
                <select 
                    name="rental_status" 
                    id="rental_status"
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-xs px-3 py-2 text-zinc-100 outline-none"
                >
                    <option value="">Semua</option>
                    <option value="waiting_payment" {{ request('rental_status') === 'waiting_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="paid" {{ request('rental_status') === 'paid' ? 'selected' : '' }}>Lunas (Siap Pickup)</option>
                    <option value="processed" {{ request('rental_status') === 'processed' ? 'selected' : '' }}>Diproses</option>
                    <option value="picked_up" {{ request('rental_status') === 'picked_up' ? 'selected' : '' }}>Aktif Disewa</option>
                    <option value="returned" {{ request('rental_status') === 'returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                    <option value="completed" {{ request('rental_status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('rental_status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="expired" {{ request('rental_status') === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                </select>
            </div>

            <!-- Submit -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 text-xs font-bold uppercase tracking-wider rounded-lg transition-all">
                    Saring
                </button>
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-bold uppercase border border-zinc-700 rounded-lg transition-all text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table List -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 sm:p-8 shadow-2xl">
        @if($orders->isEmpty())
            <div class="text-center py-12 text-zinc-550 text-sm">
                Belum ada data transaksi pesanan.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-400 font-light">
                    <thead class="bg-zinc-950 text-[10px] font-bold text-zinc-500 uppercase tracking-wider">
                        <tr>
                            <th class="p-4 rounded-l-lg">No. Pesanan</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Periode Rental</th>
                            <th class="p-4 text-right">Total Biaya</th>
                            <th class="p-4 text-center">Status Bayar</th>
                            <th class="p-4 text-center">Status Sewa</th>
                            <th class="p-4 text-center rounded-r-lg" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/65">
                        @foreach($orders as $order)
                            <tr class="hover:bg-zinc-850/40 transition-colors duration-150">
                                <td class="p-4 font-mono font-bold text-amber-500 tracking-wider">
                                    {{ $order->order_number }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-zinc-300">{{ $order->user->name }}</div>
                                    <div class="text-[11px] text-zinc-550">{{ $order->user->email }}</div>
                                </td>
                                <td class="p-4">
                                    <div>{{ $order->rental_start_date->format('d M Y') }} - {{ $order->rental_end_date->format('d M Y') }}</div>
                                    <div class="text-[10px] font-bold text-amber-500 font-mono">{{ $order->duration_days }} Hari</div>
                                </td>
                                <td class="p-4 text-right font-bold text-zinc-200 font-mono">
                                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        @if($order->payment_status === 'paid') bg-green-500/10 border border-green-500/30 text-green-400 
                                        @elseif($order->payment_status === 'pending') bg-yellow-500/10 border border-yellow-500/30 text-yellow-400
                                        @elseif($order->payment_status === 'expired') bg-red-500/10 border border-red-500/30 text-red-400
                                        @else bg-zinc-800 border border-zinc-700 text-zinc-400 @endif">
                                        {{ $order->payment_status === 'paid' ? 'Lunas' : ($order->payment_status === 'pending' ? 'Pending' : ($order->payment_status === 'expired' ? 'Expired' : 'Failed')) }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-850 border border-zinc-700 text-zinc-300">
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
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex px-3 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200 text-xs font-bold uppercase rounded-lg transition-all duration-300">
                                        Monitor
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $orders->appends(request()->input())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
