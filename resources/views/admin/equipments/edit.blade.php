@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-8 font-sans">
    
    <!-- Title -->
    <div class="border-b border-zinc-800 pb-6">
        <a href="{{ route('admin.equipments.index') }}" class="inline-flex items-center text-xs text-zinc-500 hover:text-amber-400 font-bold uppercase tracking-wider mb-2 transition-colors">
            &larr; Kembali ke Inventaris Peralatan
        </a>
        <h1 class="text-3xl font-black text-amber-500 tracking-wider uppercase">UBAH PERALATAN</h1>
    </div>

    <!-- Form card -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 sm:p-8 shadow-2xl">
        <form action="{{ route('admin.equipments.update', $equipment->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="space-y-2">
                    <label for="name" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Nama Alat</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        value="{{ old('name', $equipment->name) }}"
                        required
                        class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                        placeholder="Contoh: DJI Ronin-SC"
                    >
                    @error('name')
                        <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="space-y-2">
                    <label for="slug" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Slug</label>
                    <input 
                        type="text" 
                        name="slug" 
                        id="slug"
                        value="{{ old('slug', $equipment->slug) }}"
                        required
                        class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none font-mono text-xs"
                        placeholder="dji-ronin-sc"
                    >
                    @error('slug')
                        <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div class="space-y-2">
                    <label for="category_id" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Kategori</label>
                    <select 
                        name="category_id" 
                        id="category_id"
                        required
                        class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                    >
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $equipment->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label for="status" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Status Alat</label>
                    <select 
                        name="status" 
                        id="status"
                        required
                        class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                    >
                        <option value="ready" {{ old('status', $equipment->status) == 'ready' ? 'selected' : '' }}>Tersedia (Ready)</option>
                        <option value="maintenance" {{ old('status', $equipment->status) == 'maintenance' ? 'selected' : '' }}>Pemeliharaan (Maintenance)</option>
                        <option value="unavailable" {{ old('status', $equipment->status) == 'unavailable' ? 'selected' : '' }}>Tidak Siap (Unavailable)</option>
                    </select>
                    @error('status')
                        <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stock -->
                <div class="space-y-2">
                    <label for="stock" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Jumlah Stok</label>
                    <input 
                        type="number" 
                        name="stock" 
                        id="stock"
                        value="{{ old('stock', $equipment->stock) }}"
                        required
                        min="0"
                        class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none font-mono"
                    >
                    @error('stock')
                        <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price per day -->
                <div class="space-y-2">
                    <label for="price_per_day" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Tarif Sewa Harian (Rp)</label>
                    <input 
                        type="number" 
                        name="price_per_day" 
                        id="price_per_day"
                        value="{{ old('price_per_day', $equipment->price_per_day) }}"
                        required
                        min="0"
                        class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none font-mono"
                    >
                    @error('price_per_day')
                        <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Image path -->
            <div class="space-y-2">
                <label for="image_path" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">URL Gambar Alat</label>
                <input 
                    type="text" 
                    name="image_path" 
                    id="image_path"
                    value="{{ old('image_path', $equipment->image_path) }}"
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                    placeholder="Contoh: https://example.com/dji.jpg"
                >
                @error('image_path')
                    <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specifications -->
            <div class="space-y-2">
                <label for="specifications" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Spesifikasi <span class="text-zinc-600 font-normal normal-case">(JSON atau teks bebas)</span></label>
                <textarea 
                    name="specifications" 
                    id="specifications"
                    rows="4"
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none font-mono"
                    placeholder='{&#10;  "sensor": "Full Frame",&#10;  "recording": "4K 120fps"&#10;}&#10;&#10;Atau tulis teks bebas, sistem akan menyimpannya sebagai catatan.'
                >{{ old('specifications', $equipment->specifications ? json_encode($equipment->specifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                <p class="text-[11px] text-zinc-600 font-light">Masukkan JSON <code class="text-amber-600">{"key": "value"}</code> untuk spesifikasi terstruktur, atau teks biasa untuk catatan ringkas.</p>
                @error('specifications')
                    <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Deskripsi Lengkap</label>
                <textarea 
                    name="description" 
                    id="description"
                    rows="4"
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                    placeholder="Tuliskan keterangan detail barang disini..."
                >{{ old('description', $equipment->description) }}</textarea>
                @error('description')
                    <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4 pt-4 border-t border-zinc-850">
                <button type="submit" class="inline-flex px-6 py-3 bg-amber-500 hover:bg-amber-400 text-zinc-950 text-xs font-black uppercase tracking-widest rounded-lg transition-all duration-300 shadow-md">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.equipments.index') }}" class="inline-flex px-6 py-3 bg-zinc-800 hover:bg-zinc-700 border border-zinc-750 text-zinc-300 text-xs font-bold uppercase tracking-wider rounded-lg transition-all duration-300">
                    Batal
                </a>
            </div>

        </form>
    </div>

</div>
@endsection
