{{-- Sidebar Info Kurikulum & Jadwal Sesi Pertemuan --}}
<div class="space-y-4">
    <!-- Informasi Mata Kajian & Kurikulum -->
    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span>Informasi Mata Kajian</span>
            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $kelas->tipe?->badgeClass() }}">
                {{ $kelas->tipe?->shortLabel() }}
            </span>
        </h3>
        <div class="space-y-2.5 text-slate-600 dark:text-slate-300">
            <div>
                <span class="text-[11px] text-slate-400 block font-medium">Kategori Kurikulum:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $kelas->tipe?->label() }}</span>
            </div>
            <div>
                <span class="text-[11px] text-slate-400 block font-medium">Semester Akademik:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $kelas->periode?->nama ?? '-' }} ({{ $kelas->periode?->kode ?? '-' }})</span>
            </div>
            <div>
                <span class="text-[11px] text-slate-400 block font-medium">Deskripsi Kajian:</span>
                <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 leading-relaxed">{{ $kelas->deskripsi ?: 'Kurikulum standar pembinaan karakter dan keilmuan asrama.' }}</p>
            </div>
        </div>
    </div>

    <!-- Jadwal Sesi & Ruangan -->
    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span>Jadwal Sesi Pertemuan</span>
            <span class="text-[11px] font-mono text-slate-400">{{ $kelas->jadwal->count() }} Sesi</span>
        </h3>
        <div class="space-y-2">
            @forelse($kelas->jadwal as $j)
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/60 space-y-0.5">
                    <div class="flex items-center justify-between font-bold text-slate-800 dark:text-slate-200">
                        <span>{{ $j->hari }}</span>
                        <span class="font-mono text-[11px] text-blue-600 dark:text-blue-400">{{ substr($j->jam_mulai,0,5) }} &ndash; {{ substr($j->jam_selesai,0,5) }}</span>
                    </div>
                    <p class="text-[11px] text-slate-400">{{ $j->ruangan ? "Ruangan: {$j->ruangan}" : 'Ruangan belum ditentukan' }}</p>
                </div>
            @empty
                <p class="text-slate-400 italic text-[11px] py-1">Belum ada jadwal sesi yang diatur untuk kajian ini.</p>
            @endforelse
        </div>
    </div>
</div>

