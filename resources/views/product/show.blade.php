@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Back to catalog link -->
    <div class="mb-6">
        <a href="{{ route('catalog') }}" class="text-xs text-zinc-500 hover:text-amber-500 transition duration-150 flex items-center gap-1">
            &larr; Kembali ke Katalog
        </a>
    </div>

    <!-- Main Grid layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Left: Image & Specifications -->
        <div class="col-span-1 lg:col-span-2 space-y-6">
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm p-6 flex items-center justify-center min-h-[300px] relative overflow-hidden group">
                <!-- Large icon representing film equipment -->
                <svg class="w-24 h-24 text-zinc-800 group-hover:text-amber-500/20 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <div class="absolute bottom-4 left-6">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/25">
                        {{ $equipment->category->name ?? 'Peralatan' }}
                    </span>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-zinc-900/40 border border-zinc-900 rounded-sm p-6">
                <h2 class="text-lg font-bold text-zinc-100 mb-3 border-b border-zinc-850 pb-2">Deskripsi Alat</h2>
                <p class="text-sm text-zinc-400 font-light leading-relaxed">
                    {{ $equipment->description }}
                </p>
            </div>

            <!-- Specifications -->
            @if($equipment->specifications && count($equipment->specifications) > 0)
                <div class="bg-zinc-900/40 border border-zinc-900 rounded-sm p-6">
                    <h2 class="text-lg font-bold text-zinc-100 mb-3 border-b border-zinc-850 pb-2">Spesifikasi Teknis</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($equipment->specifications as $key => $val)
                            <div class="flex justify-between items-center text-xs py-1 border-b border-zinc-900/40">
                                <span class="text-zinc-500">{{ $key }}</span>
                                <strong class="text-zinc-300 font-semibold">{{ $val }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Rental booking controls & Real-time check via Alpine -->
        <div class="col-span-1" x-data="{ 
            startDate: '{{ old('rental_start_date') }}', 
            endDate: '{{ old('rental_end_date') }}', 
            qty: {{ old('qty', 1) }}, 
            availableUnits: null, 
            reservedUnits: null, 
            ok: false, 
            checked: false, 
            message: '', 
            loading: false,
            
            checkAvailability() {
                if (!this.startDate || !this.endDate) return;
                this.loading = true;
                fetch(`/product/{{ $equipment->slug }}/availability?rental_start_date=${this.startDate}&rental_end_date=${this.endDate}&qty=${this.qty}`)
                    .then(res => res.json())
                    .then(data => {
                        this.availableUnits = data.available_units;
                        this.reservedUnits = data.reserved_units;
                        this.ok = data.ok;
                        this.message = data.message;
                        this.checked = true;
                        this.loading = false;
                    })
                    .catch(err => {
                        this.loading = false;
                        console.error(err);
                    });
            }
        }" x-init="$watch('startDate', () => checkAvailability()); $watch('endDate', () => checkAvailability()); $watch('qty', () => checkAvailability()); if(startDate && endDate) checkAvailability();">
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm p-6 space-y-6">
                <!-- Name and price -->
                <div>
                    <h1 class="text-2xl font-bold text-zinc-100 mb-1 leading-snug">{{ $equipment->name }}</h1>
                    <div class="flex items-center gap-2 mt-2">
                        @if($equipment->status === \App\Models\Equipment::STATUS_READY)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                                Tersedia
                            </span>
                        @elseif($equipment->status === \App\Models\Equipment::STATUS_MAINTENANCE)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/25">
                                Perbaikan
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-red-500/10 text-red-400 border border-red-500/25">
                                Tidak Siap
                            </span>
                        @endif
                        <span class="text-zinc-500 text-[10px] font-light">Stok: {{ $equipment->stock }} unit</span>
                    </div>
                </div>

                <div class="border-t border-b border-zinc-850 py-4 flex justify-between items-center">
                    <span class="text-zinc-400 text-xs font-light">Tarif Sewa</span>
                    <div>
                        <span class="text-xl font-extrabold text-amber-500 font-mono">Rp {{ number_format($equipment->price_per_day, 0, ',', '.') }}</span>
                        <span class="text-zinc-500 text-[10px] font-light">/ hari</span>
                    </div>
                </div>

                <!-- Booking date Form -->
                <form method="POST" action="{{ route('cart.add') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="equipment_id" value="{{ $equipment->id }}" />

                    <!-- Start date picker -->
                    <div>
                        <label for="rental_start_date" class="block text-xs font-semibold text-zinc-400 mb-1.5">Tanggal Mulai Sewa</label>
                        <input 
                            type="date" 
                            name="rental_start_date" 
                            id="rental_start_date"
                            min="{{ date('Y-m-d') }}"
                            x-model="startDate"
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-sm text-zinc-100 text-sm focus:border-amber-500/50 focus:ring-amber-500/20 py-2 px-3 transition duration-150"
                            required
                        />
                    </div>

                    <!-- End date picker -->
                    <div>
                        <label for="rental_end_date" class="block text-xs font-semibold text-zinc-400 mb-1.5">Tanggal Akhir Sewa</label>
                        <input 
                            type="date" 
                            name="rental_end_date" 
                            id="rental_end_date"
                            min="{{ date('Y-m-d') }}"
                            x-model="endDate"
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-sm text-zinc-100 text-sm focus:border-amber-500/50 focus:ring-amber-500/20 py-2 px-3 transition duration-150"
                            required
                        />
                    </div>

                    <!-- Quantity input -->
                    <div>
                        <label for="qty" class="block text-xs font-semibold text-zinc-400 mb-1.5">Jumlah Unit</label>
                        <input 
                            type="number" 
                            name="qty" 
                            id="qty"
                            min="1"
                            max="{{ $equipment->stock }}"
                            x-model="qty"
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-sm text-zinc-100 text-sm focus:border-amber-500/50 focus:ring-amber-500/20 py-2 px-3 transition duration-150"
                            required
                        />
                    </div>

                    <!-- Real-time Availability checking feedback -->
                    <div class="bg-zinc-950/60 p-4 rounded-sm border border-zinc-850 space-y-3" x-show="startDate && endDate">
                        <div class="flex items-center justify-between text-xs border-b border-zinc-850 pb-2">
                            <span class="text-zinc-500 font-light">Status Unit</span>
                            <div class="flex items-center gap-1.5 font-bold">
                                <template x-if="loading">
                                    <span class="text-zinc-500">Mengecek...</span>
                                </template>
                                <template x-if="!loading && checked">
                                    <span :class="ok ? 'text-emerald-400' : 'text-red-400'" x-text="ok ? 'Tersedia' : 'Penuh/Tidak Tersedia'"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Buffer warnings note -->
                        <div class="text-[10px] leading-relaxed text-zinc-400/80 font-light" x-show="checked && !loading">
                            <p x-text="message" :class="ok ? 'text-zinc-300' : 'text-red-400/90'"></p>
                            <div class="mt-2 text-zinc-500 border-t border-zinc-900 pt-2 flex flex-col gap-0.5">
                                <span>Note: Kebijakan buffer operasional memblokir 1 hari sebelum dan sesudah masa sewa untuk pemeliharaan unit alat media.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit triggers -->
                    @guest
                        <a href="{{ route('login') }}" class="block w-full text-center bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold py-3 rounded-sm text-sm transition duration-150">
                            Masuk untuk Menyewa
                        </a>
                    @else
                        <button 
                            type="submit" 
                            x-bind:disabled="!ok || loading"
                            class="w-full font-bold py-3 rounded-sm text-sm transition duration-150 shrink-0"
                            :class="ok && !loading ? 'bg-amber-500 hover:bg-amber-600 text-zinc-950 cursor-pointer' : 'bg-zinc-800 text-zinc-500 cursor-not-allowed'"
                        >
                            <span x-show="!loading">Tambahkan ke Keranjang</span>
                            <span x-show="loading">Mengecek Ketersediaan...</span>
                        </button>
                    @endguest
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
