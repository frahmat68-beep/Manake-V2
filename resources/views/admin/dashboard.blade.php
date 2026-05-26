@extends('layouts.admin')

@section('content')
<div class="space-y-8 font-sans">
    
    <!-- Title -->
    <div>
        <h1 class="text-3xl font-black text-amber-500 tracking-wider uppercase">ADMIN DASHBOARD</h1>
        <p class="text-xs text-zinc-500 font-light mt-1">Status operasional, keuangan, dan monitoring transaksi rental alat media Manake.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Equipment -->
        <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-xl flex items-center justify-between shadow-xl">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">Total Alat</span>
                <span class="text-3xl font-black text-zinc-100 font-mono">{{ $totalEquipment }}</span>
                <span class="text-[11px] text-zinc-400 block">{{ $readyEquipment }} Siap Sewa</span>
            </div>
            <div class="p-3 bg-amber-500/10 text-amber-400 rounded-lg border border-amber-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
            </div>
        </div>

        <!-- Maintenance Equipment -->
        <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-xl flex items-center justify-between shadow-xl">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">Dalam Perbaikan</span>
                <span class="text-3xl font-black text-red-500 font-mono">{{ $maintenanceEquipment }}</span>
                <span class="text-[11px] text-zinc-500 block">Unit dalam pemeliharaan QC</span>
            </div>
            <div class="p-3 bg-red-500/10 text-red-400 rounded-lg border border-red-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
            </div>
        </div>

        <!-- Orders Waiting Action -->
        <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-xl flex items-center justify-between shadow-xl">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">Menunggu Tindakan</span>
                <span class="text-3xl font-black text-yellow-500 font-mono">{{ $ordersWaitingAction }}</span>
                <span class="text-[11px] text-zinc-400 block">{{ $pendingPayments }} Belum Bayar</span>
            </div>
            <div class="p-3 bg-yellow-500/10 text-yellow-400 rounded-lg border border-yellow-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Revenue -->
        <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-xl flex items-center justify-between shadow-xl">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">Total Pendapatan</span>
                <span class="text-xl font-extrabold text-green-400 font-mono">Rp {{ number_format($revenuePaidTotal, 0, ',', '.') }}</span>
                <span class="text-[11px] text-zinc-400 block">{{ $paidOrdersCount }} Transaksi Lunas</span>
            </div>
            <div class="p-3 bg-green-500/10 text-green-400 rounded-lg border border-green-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

    </div>

    <!-- Recent Orders Dashboard Section -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 sm:p-8 shadow-2xl">
        <div class="flex justify-between items-center border-b border-zinc-800 pb-4 mb-6">
            <h3 class="text-sm font-bold text-zinc-300 uppercase tracking-wider">5 Transaksi Terbaru</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-amber-500 hover:text-amber-400 hover:underline tracking-wider font-bold">
                Lihat Semua Pesanan &rarr;
            </a>
        </div>

        @if($latestOrders->isEmpty())
            <div class="text-center py-12 text-zinc-500 text-sm">
                Belum ada data transaksi pemesanan rental.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-400">
                    <thead class="bg-zinc-950 text-[10px] font-bold text-zinc-500 uppercase tracking-wider">
                        <tr>
                            <th class="p-4 rounded-l-lg">No. Pesanan</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Tanggal Sewa</th>
                            <th class="p-4 text-right">Total Biaya</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/65 font-light">
                        @foreach($latestOrders as $order)
                            <tr class="hover:bg-zinc-850/40 transition-colors duration-150">
                                <td class="p-4 font-mono font-bold text-amber-500 tracking-wider">
                                    {{ $order->order_number }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-zinc-300">{{ $order->user->name }}</div>
                                    <div class="text-[11px] text-zinc-550">{{ $order->user->email }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="text-zinc-300">{{ $order->rental_start_date->format('d M Y') }} - {{ $order->rental_end_date->format('d M Y') }}</div>
                                    <div class="text-[11px] text-amber-500/80 font-bold font-mono">{{ $order->duration_days }} Hari</div>
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
                                        {{ $order->payment_status === 'paid' ? 'Lunas' : ($order->payment_status === 'pending' ? 'Pending' : 'Expired') }}
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
        @endif
    </div>

</div>
@endsection
