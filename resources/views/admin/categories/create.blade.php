@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-8 font-sans">
    
    <!-- Title -->
    <div class="border-b border-zinc-800 pb-6">
        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center text-xs text-zinc-500 hover:text-amber-400 font-bold uppercase tracking-wider mb-2 transition-colors">
            &larr; Kembali ke Daftar Kategori
        </a>
        <h1 class="text-3xl font-black text-amber-500 tracking-wider uppercase">TAMBAH KATEGORI</h1>
    </div>

    <!-- Form card -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 sm:p-8 shadow-2xl">
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Name -->
            <div class="space-y-2">
                <label for="name" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Nama Kategori</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                    placeholder="Contoh: Kamera Mirrorless"
                >
                @error('name')
                    <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug -->
            <div class="space-y-2">
                <label for="slug" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Slug (Opsional)</label>
                <input 
                    type="text" 
                    name="slug" 
                    id="slug"
                    value="{{ old('slug') }}"
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none font-mono text-xs"
                    placeholder="contoh-kamera-mirrorless (dibuat otomatis jika dikosongkan)"
                >
                @error('slug')
                    <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="text-xs font-bold text-zinc-400 uppercase tracking-widest block">Deskripsi</label>
                <textarea 
                    name="description" 
                    id="description"
                    rows="4"
                    class="w-full bg-zinc-950 border border-zinc-850 focus:border-amber-500 rounded-lg text-sm px-4 py-3 text-zinc-100 placeholder-zinc-700 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                    placeholder="Deskripsikan kelompok alat ini..."
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-red-500 font-light mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4 pt-4 border-t border-zinc-850">
                <button type="submit" class="inline-flex px-6 py-3 bg-amber-500 hover:bg-amber-400 text-zinc-950 text-xs font-black uppercase tracking-widest rounded-lg transition-all duration-300 shadow-md">
                    Simpan Kategori
                </button>
                <a href="{{ route('admin.categories.index') }}" class="inline-flex px-6 py-3 bg-zinc-800 hover:bg-zinc-700 border border-zinc-750 text-zinc-300 text-xs font-bold uppercase tracking-wider rounded-lg transition-all duration-300">
                    Batal
                </a>
            </div>

        </form>
    </div>

</div>
@endsection
