<!-- Kontrol Semester & Periode Akademik -->
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="size-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Kontrol Semester & Periode Akademik</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih semester operasional aktif. Filter penempatan kamar & sinkronisasi otomatis merujuk ke periode ini.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <span class="text-[11px] font-medium text-slate-400">Status Saat Ini:</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-slate-800 dark:text-indigo-300 border border-indigo-200/80 dark:border-slate-700">
                <span class="size-2 rounded-full bg-indigo-500 animate-pulse"></span>
                {{ $activePeriode?->nama ?? 'Belum Ditentukan' }} ({{ $activePeriode?->kode ?? '-' }})
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.cache.periode.switch') }}" class="p-4 sm:p-5 space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse ($periodes as $p)
                <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all {{ $p->is_active ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/20 ring-2 ring-indigo-500/20' : 'border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900/50' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="space-y-0.5">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $p->nama }}</span>
                            <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400">Kode: {{ $p->kode }}</span>
                        </div>
                        <input type="radio" name="periode_id" value="{{ $p->id }}" {{ $p->is_active ? 'checked' : '' }} class="mt-1 size-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                        <span>{{ $p->tanggal_mulai ? $p->tanggal_mulai->format('d M Y') : '-' }} s/d {{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d M Y') : '-' }}</span>
                        @if ($p->is_active)
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">● Sedang Aktif</span>
                        @endif
                    </div>
                </label>
            @empty
                <div class="col-span-full py-6 text-center text-xs text-slate-400">
                    Belum ada data periode akademik yang tersimpan di basis data.
                </div>
            @endforelse
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 dark:border-slate-800/80">
            <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                <svg class="size-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Mengubah semester aktif seketika mereset cache <code class="font-mono text-slate-500 dark:text-slate-300">active_periode</code> tanpa downtime.</span>
            </p>
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-2xs transition-colors cursor-pointer shrink-0">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Terapkan Semester Aktif</span>
            </button>
        </div>
    </form>
</div>

