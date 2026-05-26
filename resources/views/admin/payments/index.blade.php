@extends('layouts.admin')

@section('content')
<div class="space-y-8 font-sans">
    
    <!-- Title -->
    <div>
        <h1 class="text-3xl font-black text-amber-500 tracking-wider uppercase">LOG PEMBAYARAN</h1>
        <p class="text-xs text-zinc-500 font-light mt-1">Audit log data transaksi yang diproses via gateway pembayaran online Midtrans.</p>
    </div>

    <!-- Table List -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 sm:p-8 shadow-2xl">
        @if($payments->isEmpty())
            <div class="text-center py-12 text-zinc-550 text-sm">
                Belum ada data riwayat pembayaran online yang tercatat.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-400 font-light">
                    <thead class="bg-zinc-950 text-[10px] font-bold text-zinc-500 uppercase tracking-wider">
                        <tr>
                            <th class="p-4 rounded-l-lg">No. Pesanan</th>
                            <th class="p-4">ID Transaksi Midtrans</th>
                            <th class="p-4">Metode</th>
                            <th class="p-4 text-right">Jumlah Gross</th>
                            <th class="p-4 text-center">Status Transaksi</th>
                            <th class="p-4 text-center">Waktu Bayar</th>
                            <th class="p-4 text-center rounded-r-lg" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/65 font-mono text-xs">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-zinc-850/40 transition-colors duration-150 text-[11px]">
                                <td class="p-4 font-bold text-amber-500">
                                    {{ $payment->order->order_number ?? '-' }}
                                </td>
                                <td class="p-4 text-zinc-500">
                                    {{ $payment->midtrans_order_id }}
                                </td>
                                <td class="p-4 uppercase text-zinc-400 font-sans">
                                    {{ $payment->payment_type ?? 'Snap' }}
                                </td>
                                <td class="p-4 text-right font-bold text-zinc-200">
                                    Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider font-sans
                                        @if($payment->status === 'paid') bg-green-500/10 border border-green-500/30 text-green-400 
                                        @elseif($payment->status === 'pending') bg-yellow-500/10 border border-yellow-500/30 text-yellow-400
                                        @elseif($payment->status === 'expired') bg-red-500/10 border border-red-500/30 text-red-400
                                        @else bg-zinc-800 border border-zinc-700 text-zinc-400 @endif">
                                        {{ $payment->transaction_status ?? $payment->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-center font-sans text-zinc-450">
                                    {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="p-4 text-center font-sans">
                                    @if($payment->order)
                                        <a href="{{ route('admin.orders.show', $payment->order->id) }}" class="inline-flex px-2 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200 text-[10px] font-bold uppercase rounded transition-all">
                                            Monitor
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
