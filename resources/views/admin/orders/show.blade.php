@extends('layouts.admin')

@section('content')
<div class="space-y-8 font-sans">
    
    <!-- Title -->
    <div>
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center text-xs text-zinc-500 hover:text-amber-400 font-bold uppercase tracking-wider mb-2 transition-colors">
            &larr; Kembali ke Monitoring Pesanan
        </a>
        <h1 class="text-3xl font-black text-amber-500 tracking-wider uppercase">MONITOR PESANAN</h1>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg text-sm flex items-center shadow-lg">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Left Column: Details & Items -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Reservation info -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 sm:p-8 shadow-xl space-y-6">
                <div class="flex justify-between items-start border-b border-zinc-800 pb-4">
                    <div>
                        <span class="text-[10px] font-bold text-zinc-550 uppercase tracking-widest block">KODE RESERVASI</span>
                        <h2 class="text-xl font-bold font-mono text-zinc-100 tracking-wider">{{ $order->order_number }}</h2>
                        <span class="text-xs text-zinc-500">Invoice: {{ $order->midtrans_order_id }}</span>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            @if($order->payment_status === 'paid') bg-green-500/10 border border-green-500/30 text-green-400 
                            @elseif($order->payment_status === 'pending') bg-yellow-500/10 border border-yellow-500/30 text-yellow-400
                            @elseif($order->payment_status === 'expired') bg-red-500/10 border border-red-500/30 text-red-400
                            @else bg-zinc-800 border border-zinc-700 text-zinc-400 @endif">
                            Bayar: {{ $order->payment_status }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-850 border border-zinc-700 text-zinc-300">
                            Sewa: {{ $order->rental_status }}
                        </span>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-light">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">CUSTOMER</span>
                        <div class="font-bold text-zinc-300 text-sm">{{ $order->user->name }}</div>
                        <div class="text-zinc-400">Email: {{ $order->user->email }}</div>
                        <div class="text-zinc-400">Telp: {{ $order->user->profile->phone ?? '-' }}</div>
                    </div>
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">IDENTITAS & ALAMAT</span>
                        <div class="text-zinc-300">NIK: {{ $order->user->profile->identity_number ?? '-' }}</div>
                        <div class="text-zinc-400">Alamat: {{ $order->user->profile->address ?? '-' }}</div>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid #27272a;">

                <!-- Period details -->
                <div class="grid grid-cols-3 gap-4 bg-zinc-950 p-4 border border-zinc-850 rounded-xl text-xs font-light text-center">
                    <div>
                        <span class="text-[9px] font-bold text-zinc-550 uppercase tracking-widest block mb-1">MULAI</span>
                        <span class="font-bold text-zinc-300">{{ $order->rental_start_date->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-zinc-550 uppercase tracking-widest block mb-1">SELESAI</span>
                        <span class="font-bold text-zinc-300">{{ $order->rental_end_date->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-zinc-550 uppercase tracking-widest block mb-1">DURASI</span>
                        <span class="font-bold text-amber-500">{{ $order->duration_days }} Hari</span>
                    </div>
                </div>
            </div>

            <!-- Staged items list -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 sm:p-8 shadow-xl">
                <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-4 border-b border-zinc-800 pb-2">DAFTAR ITEM YANG DISEWA</h3>
                <div class="divide-y divide-zinc-800">
                    @foreach($order->items as $item)
                        <div class="py-4 first:pt-0 last:pb-0 flex justify-between items-center gap-4 text-xs font-light">
                            <div class="space-y-1">
                                <h4 class="font-bold text-zinc-200 text-sm">{{ $item->equipment_name }}</h4>
                                <div class="text-zinc-500">
                                    {{ $item->qty }} unit &times; Rp {{ number_format($item->price_per_day, 0, ',', '.') }} / hari
                                </div>
                            </div>
                            <span class="font-bold font-mono text-zinc-300 text-sm">
                                Rp {{ number_format($item->item_subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-zinc-800 pt-4 mt-4 space-y-2 max-w-xs ml-auto text-xs font-light">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Subtotal Sewa</span>
                        <span class="font-bold font-mono text-zinc-300">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">PPN (11%)</span>
                        <span class="font-bold font-mono text-zinc-300">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Biaya Tambahan</span>
                        <span class="font-bold font-mono text-zinc-300">Rp {{ number_format($order->additional_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-zinc-800 pt-2 text-sm font-bold">
                        <span class="text-amber-500">GRAND TOTAL</span>
                        <span class="font-mono text-zinc-200">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Status Logs audit trail -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 shadow-xl">
                <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-4 border-b border-zinc-800 pb-2">AUDIT STATUS LOGS</h3>
                <div class="space-y-4">
                    @forelse($order->statusLogs as $log)
                        <div class="p-4 bg-zinc-950 border border-zinc-850 rounded-lg text-xs font-light space-y-2">
                            <div class="flex justify-between items-center text-[10px] font-bold text-zinc-500 uppercase tracking-wider">
                                <span>Aktor: {{ $log->actor_type }} ({{ $log->user->name ?? 'System' }})</span>
                                <span>{{ $log->created_at->format('d M Y H:i') }} WIB</span>
                            </div>
                            <p class="text-zinc-300 leading-relaxed">{{ $log->note }}</p>
                            @if($log->additional_fee > 0)
                                <div class="text-amber-500 font-bold font-mono text-[10px]">+ Rp {{ number_format($log->additional_fee, 0, ',', '.') }} Denda</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-center py-4 text-zinc-500 text-xs">Belum ada catatan log audit.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Column: Operations Forms -->
        <div class="space-y-6">
            
            <!-- Update Status form -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 shadow-xl space-y-4">
                <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider border-b border-zinc-800 pb-2">OPERASIONAL STATUS SEWA</h3>
                
                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-2">
                        <label for="status" class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">Pilih Status Baru</label>
                        <select 
                            name="status" 
                            id="status"
                            class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-xs px-3 py-2 text-zinc-100 outline-none"
                        >
                            <option value="waiting_payment" {{ $order->rental_status === 'waiting_payment' ? 'selected' : '' }}>Menunggu Pembayaran (Waiting Payment)</option>
                            <option value="paid" {{ $order->rental_status === 'paid' ? 'selected' : '' }}>Lunas / Siap Serah Terima (Paid)</option>
                            <option value="processed" {{ $order->rental_status === 'processed' ? 'selected' : '' }}>Diproses (Processed)</option>
                            <option value="picked_up" {{ $order->rental_status === 'picked_up' ? 'selected' : '' }}>Aktif Disewa (Picked Up)</option>
                            <option value="returned" {{ $order->rental_status === 'returned' ? 'selected' : '' }}>Sudah Dikembalikan (Returned)</option>
                            <option value="damaged" {{ $order->rental_status === 'damaged' ? 'selected' : '' }}>Rusak (Damaged)</option>
                            <option value="lost" {{ $order->rental_status === 'lost' ? 'selected' : '' }}>Hilang (Lost)</option>
                            <option value="completed" {{ $order->rental_status === 'completed' ? 'selected' : '' }}>Selesai / Terarsip (Completed)</option>
                            <option value="cancelled" {{ $order->rental_status === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                            <option value="expired" {{ $order->rental_status === 'expired' ? 'selected' : '' }}>Kedaluwarsa (Expired)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-zinc-950 text-xs font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        Perbarui Status
                    </button>
                </form>
            </div>

            <!-- Add Fee form -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 shadow-xl space-y-4">
                <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider border-b border-zinc-800 pb-2">PENILAIAN DENDA / BIAYA LAIN</h3>
                
                <form action="{{ route('admin.orders.fees', $order->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Fee type -->
                    <div class="space-y-2">
                        <label for="fee_type" class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">Jenis Denda</label>
                        <select 
                            name="fee_type" 
                            id="fee_type"
                            required
                            class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-xs px-3 py-2 text-zinc-100 outline-none"
                        >
                            <option value="late">Denda Keterlambatan (Late)</option>
                            <option value="damage">Denda Kerusakan (Damage)</option>
                            <option value="lost">Denda Kehilangan (Lost)</option>
                            <option value="other">Biaya Tambahan Lain (Other)</option>
                        </select>
                    </div>

                    <!-- Amount -->
                    <div class="space-y-2">
                        <label for="amount" class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">Jumlah Denda (Rp)</label>
                        <input 
                            type="number" 
                            name="amount" 
                            id="amount"
                            required
                            min="1"
                            placeholder="Contoh: 50000"
                            class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-xs px-3 py-2 text-zinc-100 placeholder-zinc-700 outline-none font-mono"
                        >
                    </div>

                    <!-- Note -->
                    <div class="space-y-2">
                        <label for="note" class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block">Catatan / Alasan</label>
                        <input 
                            type="text" 
                            name="note" 
                            id="note"
                            required
                            placeholder="Keterlambatan 1 hari / LCD pecah"
                            class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-xs px-3 py-2 text-zinc-100 placeholder-zinc-700 outline-none"
                        >
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-red-500/10 hover:bg-red-500 border border-red-500/20 text-red-400 hover:text-white text-xs font-bold uppercase rounded-lg transition-all duration-300">
                        Catat Biaya Tambahan
                    </button>
                </form>
            </div>

            <!-- Midtrans Payment status info -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 shadow-xl space-y-4">
                <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider border-b border-zinc-800 pb-2">DATA PEMBAYARAN ONLINE</h3>
                @if($order->payment)
                    <div class="text-xs font-light space-y-2">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">ID Order Midtrans:</span>
                            <span class="font-bold text-zinc-300 font-mono">{{ $order->payment->midtrans_order_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Metode Bayar:</span>
                            <span class="font-bold text-zinc-300 uppercase">{{ $order->payment->payment_type ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Transaction Status:</span>
                            <span class="font-bold text-zinc-300 uppercase font-mono">{{ $order->payment->transaction_status ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Fraud Status:</span>
                            <span class="font-bold text-zinc-300 uppercase font-mono">{{ $order->payment->fraud_status ?? '-' }}</span>
                        </div>
                        @if($order->isPaid())
                            <div class="pt-2">
                                <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="w-full inline-flex items-center justify-center px-4 py-2 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200 text-xs font-bold uppercase rounded-lg transition-all duration-300">
                                    Buka Invoice Cetak
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-zinc-500 text-xs font-light">Data pembayaran belum tercatat.</p>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
