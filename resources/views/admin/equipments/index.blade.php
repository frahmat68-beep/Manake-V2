@extends('layouts.admin')

@section('content')
<div class="space-y-8 font-sans">
    
    <!-- Title -->
    <div class="flex justify-between items-center border-b border-zinc-800 pb-6">
        <div>
            <h1 class="text-3xl font-black text-amber-500 tracking-wider uppercase">INVENTARIS PERALATAN</h1>
            <p class="text-xs text-zinc-500 font-light mt-1">Mengelola persediaan alat sewa media, tarif sewa harian, dan ketersediaan stok.</p>
        </div>
        <a href="{{ route('admin.equipments.create') }}" class="inline-flex px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 text-xs font-bold uppercase tracking-wider rounded-lg transition-all duration-300 shadow-md">
            Tambah Peralatan
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg text-sm flex items-center shadow-lg">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 sm:p-8 shadow-2xl">
        @if($equipments->isEmpty())
            <div class="text-center py-12 text-zinc-550 text-sm">
                Belum ada data peralatan. Silakan tambahkan peralatan baru.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-400 font-light">
                    <thead class="bg-zinc-950 text-[10px] font-bold text-zinc-500 uppercase tracking-wider">
                        <tr>
                            <th class="p-4 rounded-l-lg">Peralatan</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4 text-center">Stok</th>
                            <th class="p-4 text-right">Tarif / Hari</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center rounded-r-lg" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/65">
                        @foreach($equipments as $equipment)
                            <tr class="hover:bg-zinc-850/40 transition-colors duration-150">
                                <td class="p-4 font-bold text-zinc-200">
                                    {{ $equipment->name }}
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded bg-zinc-800 border border-zinc-700 text-zinc-300 text-[11px] font-medium uppercase">
                                        {{ $equipment->category->name }}
                                    </span>
                                </td>
                                <td class="p-4 text-center font-bold text-zinc-300 font-mono">
                                    {{ $equipment->stock }}
                                </td>
                                <td class="p-4 text-right font-mono font-bold text-zinc-200">
                                    Rp {{ number_format($equipment->price_per_day, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        @if($equipment->status === 'ready') bg-green-500/10 border border-green-500/30 text-green-400 
                                        @elseif($equipment->status === 'maintenance') bg-red-500/10 border border-red-500/30 text-red-400
                                        @else bg-zinc-800 border border-zinc-700 text-zinc-400 @endif">
                                        {{ $equipment->status === 'ready' ? 'Tersedia' : ($equipment->status === 'maintenance' ? 'Maintenance' : 'Tidak Siap') }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.equipments.edit', $equipment->id) }}" class="inline-flex px-3 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-zinc-200 text-xs font-bold uppercase rounded-lg transition-all duration-300">
                                            Ubah
                                        </a>
                                        
                                        <form action="{{ route('admin.equipments.destroy', $equipment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alat ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex px-3 py-1 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white border border-red-500/20 rounded-lg text-xs font-bold uppercase transition-all duration-300">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $equipments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
