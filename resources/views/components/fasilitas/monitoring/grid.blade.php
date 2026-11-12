<!-- Grid Kamar Interaktif (Atomic Component) -->
<div class="space-y-4">
    @if($kamarList->count() > 0)
        <!-- Baris Info Hasil Filter / Jumlah Kamar -->
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 px-0.5">
            <div>
                Menampilkan <span class="font-bold text-slate-800 dark:text-slate-200">{{ $kamarList->firstItem() }}–{{ $kamarList->lastItem() }}</span> dari <span class="font-bold text-slate-800 dark:text-slate-200">{{ $kamarList->total() }}</span> unit kamar
            </div>
            @if(request()->hasAny(['gedung_id', 'lantai_id', 'status', 'gender', 'tipe_ruang', 'search']))
                <x-badge variant="info">
                    Filter Aktif
                </x-badge>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-4.5 lg:gap-5">
            @foreach($kamarList as $kamar)
                <x-kamar-card :kamar="$kamar" :editable="false" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-3">
            {{ $kamarList->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-10 text-center">
            <div class="size-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Tidak ada kamar ditemukan</h4>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                Coba sesuaikan kata kunci pencarian atau ubah filter gedung/lantai di toolbar atas.
            </p>
        </div>
    @endif
</div>
