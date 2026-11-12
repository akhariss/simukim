{{-- Dashboard Mahasantri --}}
<div class="space-y-4">
    <!-- Ringkasan Informasi Akademik & Asrama Mahasantri -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- 1. Kamar Asrama & Rekan Sekamar -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Hunian Asrama</span>
                    <span class="size-2 rounded-full {{ $kamar ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                </div>
                @if($kamar)
                    <div class="mt-2">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Kamar {{ $kamar->nomor_kamar }}</h3>
                        <p class="text-xs text-slate-500">{{ $kamar->lantai?->gedung?->nama ?? 'Gedung Asrama' }} &bull; Lt. {{ $kamar->lantai?->nomor_lantai ?? 1 }}</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <p class="font-semibold text-slate-700 dark:text-slate-300 text-[11px] mb-1">Teman Sekamar ({{ $roommates->count() }}):</p>
                        <div class="space-y-1">
                            @forelse($roommates as $rm)
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-700 dark:text-slate-300 truncate max-w-[130px]">{{ $rm->user->name ?? 'Mahasantri' }}</span>
                                    <span class="text-[10px] font-mono text-slate-400">{{ $rm->nomor_induk }}</span>
                                </div>
                            @empty
                                <p class="text-[11px] text-slate-400 italic">Belum ada rekan sekamar lain.</p>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-slate-400">Penempatan kamar belum ditetapkan.</div>
                @endif
            </div>
            <div class="pt-2 text-[11px] text-slate-400">
                Kapasitas: {{ $kamar?->kapasitas_maks ?? 4 }} Santri
            </div>
        </div>

        <!-- 2. Kelompok Halaqah & Pengampu -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Kelompok Halaqah</span>
                    <a href="{{ route('kelas.santri.index') }}" class="text-[11px] font-bold text-blue-600 hover:underline">Detail &rarr;</a>
                </div>
                @if($myKelompok)
                    <div class="mt-2">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate">{{ $myKelompok->nama }}</h3>
                        <p class="text-xs text-slate-500">{{ ($santri?->gender?->value ?? $santri?->gender) === 'L' ? 'Ikhwan' : 'Akhwat' }} &bull; {{ $myKelompok->pengampu->count() }} Mata Kajian</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs space-y-1">
                        @forelse($myKelompok->pengampu as $p)
                            <div class="flex justify-between items-center text-[11px] text-slate-700 dark:text-slate-300">
                                <span class="truncate max-w-[120px]">{{ $p->kelas->nama ?? 'Mata Kajian' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $p->tutor->name ?? '-' }}</span>
                            </div>
                        @empty
                            <p class="text-[11px] text-slate-400 italic">Belum ada tutor pengampu.</p>
                        @endforelse
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-slate-400">Belum terdaftar di kelompok.</div>
                @endif
            </div>
            <a href="{{ route('kelas.santri.index') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">
                Lihat Rekan Kelompok &rarr;
            </a>
        </div>

        <!-- 3. Jadwal Kajian Hari Ini -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Jadwal Hari Ini</span>
                    <span class="text-[10px] font-mono font-bold text-blue-600 dark:text-blue-400">{{ \Carbon\Carbon::now()->translatedFormat('l') }}</span>
                </div>
                <div class="mt-2 space-y-2">
                    @forelse($todayJadwal ?? [] as $j)
                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs space-y-0.5">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $j->kelas_nama }}</p>
                            <p class="text-[10.5px] text-slate-500 font-mono">{{ $j->jam }} WIB &bull; {{ $j->ruangan }}</p>
                            <p class="text-[10.5px] text-slate-400">Tutor: {{ $j->tutor_nama }}</p>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-slate-400">
                            Tidak ada jadwal kajian pada hari {{ \Carbon\Carbon::now()->translatedFormat('l') }}.
                        </div>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('kegiatan.index') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">
                Lihat Agenda Lengkap &rarr;
            </a>
        </div>

        <!-- 4. Capaian Akademik & Nilai -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Capaian Akademik</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($gradedCount ?? 0) > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $gradedCount ?? 0 }} / {{ $rekapNilaiCount ?? 0 }} Dinilai
                    </span>
                </div>
                <div class="mt-3 space-y-2">
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Evaluasi nilai capaian belajar kajian taklim Ma'had Al-Jami'ah semester aktif.
                    </p>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-xs flex justify-between items-center">
                        <span class="text-slate-500">Status Penilaian:</span>
                        <span class="font-bold {{ ($gradedCount ?? 0) > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ ($gradedCount ?? 0) > 0 ? 'Terekam' : 'Belum Ada Nilai' }}
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('kelas.santri.index') }}" class="w-full flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-2xs transition">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Lihat Lembar Nilai &amp; Presensi</span>
            </a>
        </div>
    </div>
</div>
