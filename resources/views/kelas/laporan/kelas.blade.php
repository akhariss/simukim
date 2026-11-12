<x-app-layout title="Laporan Bulanan — {{ $kelas->nama }}">
    <div class="space-y-4 sm:space-y-5">
        {{-- Header & Breadcrumbs --}}
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kajian Saya', 'url' => route('tutor.kajian.index')],
                ['label' => 'Laporan Bulanan', 'url' => route('kelas.laporan.index')],
                ['label' => $kelas->nama . ' (' . $kelompok->nama . ')']
            ]"
            title="{{ $kelas->nama }}"
            description="Kelompok Halaqah: {{ $kelompok->nama }}"
        >
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('kelas.laporan.index') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span class="hidden sm:inline">Daftar Kelas</span>
                    </a>
                    <a href="{{ route('kelas.laporan.create', [$kelompok, $kelas]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 transition shadow-xs cursor-pointer">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Laporan Bulanan</span>
                    </a>
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- Ringkasan Status Kelas & Bulan Berjalan --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">Mata Kajian & Tipe</span>
                    <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $kelas->nama }}</span>
                    <span class="text-[11px] text-slate-500 block">Tipe: {{ $kelas->tipe->label() }}</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">Jadwal Sesi Halaqah</span>
                    @if($kelas->jadwal->isNotEmpty())
                        <div class="space-y-0.5">
                            @foreach($kelas->jadwal as $j)
                                <p class="text-xs text-slate-800 dark:text-slate-200">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $j->nama_sesi }}:</span>
                                    <span>{{ $j->hari }}, {{ substr($j->jam_mulai, 0, 5) }}-{{ substr($j->jam_selesai, 0, 5) }} WIB</span>
                                </p>
                            @endforeach
                        </div>
                    @else
                        <span class="font-bold text-slate-900 dark:text-white text-sm">Sesuai Jadwal</span>
                        <span class="text-[11px] text-slate-500 block">Ruang Kajian Asrama</span>
                    @endif
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">Status Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</span>
                    @php
                        $curMonthLap = $riwayatLaporan->where('bulan', (int) date('m'))->where('tahun', (int) date('Y'))->first();
                    @endphp
                    @if($curMonthLap)
                        <div class="flex items-center gap-2 mt-0.5">
                            <x-badge :class="$curMonthLap->status->badgeClass()">{{ $curMonthLap->status->label() }}</x-badge>
                            <span class="text-[11px] text-slate-400 font-mono">Terkirim</span>
                        </div>
                    @else
                        <div class="flex items-center justify-between mt-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                Belum Terkirim
                            </span>
                            <a href="{{ route('kelas.laporan.create', [$kelompok, $kelas]) }}" class="text-[11px] font-bold text-blue-600 hover:underline">Kirim Sekarang &rarr;</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Riwayat Laporan Bulanan Kelas Ini --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Riwayat Laporan Bulanan Kelas Ini</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Arsip rekap laporan dan dokumentasi kegiatan per bulan.</p>
                </div>
                <span class="text-xs font-semibold text-slate-500">{{ $riwayatLaporan->count() }} Laporan Terekam</span>
            </div>

            <div class="space-y-3">
                @forelse($riwayatLaporan as $lap)
                    <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-slate-900 dark:text-white">{{ $lap->nama_bulan }} {{ $lap->tahun }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">({{ date('d M Y', strtotime($lap->created_at)) }})</span>
                            </div>
                            <x-badge :class="$lap->status->badgeClass()">{{ $lap->status->label() }}</x-badge>
                        </div>

                        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $lap->catatan }}</p>

                        @if($lap->catatan_admin)
                            <div class="p-2.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200/60 dark:border-blue-900/40 text-[11px] text-blue-900 dark:text-blue-200">
                                <span class="font-bold">Catatan Verifikasi Admin:</span> {{ $lap->catatan_admin }}
                            </div>
                        @endif

                        @if($lap->foto->isNotEmpty())
                            <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Dokumentasi Foto ({{ $lap->foto->count() }} Foto):</span>
                                <div class="flex flex-wrap gap-2.5">
                                    @foreach($lap->foto as $f)
                                        @php
                                            $secureUrl = route('secure.file', ['category' => 'laporan-kelas', 'filename' => basename($f->file_path)]);
                                        @endphp
                                        <a href="{{ $secureUrl }}" target="_blank" class="block size-20 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:opacity-85 transition shrink-0 group relative shadow-2xs">
                                            <img src="{{ $secureUrl }}" alt="{{ $f->file_name }}" class="w-full h-full object-cover">
                                            <span class="absolute bottom-0 inset-x-0 bg-slate-900/70 text-white text-[9px] px-1 py-0.5 text-center truncate font-mono">{{ $f->file_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Action Buttons: Detail, Edit, Hapus --}}
                        <div class="pt-2.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs">
                            <a href="{{ route('kelas.laporan.show', $lap->id) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                <span>Lihat Detail Laporan &rarr;</span>
                            </a>
                            <div class="flex items-center gap-1.5">
                                @if(($lap->status->value ?? $lap->status) === 'menunggu_review')
                                    <a href="{{ route('kelas.laporan.edit', $lap->id) }}" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 hover:bg-amber-100 transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('kelas.laporan.destroy', $lap->id) }}" method="POST" class="inline" data-confirm="Hapus laporan bulanan ini beserta seluruh foto dokumentasinya?" data-confirm-title="Hapus Laporan Bulanan" data-confirm-type="danger">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 hover:bg-rose-100 transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Laporan Terkunci</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center space-y-3">
                        <div class="size-12 rounded-2xl bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-xs text-slate-800 dark:text-slate-200">Belum Ada Laporan Terkirim</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Silakan kirimkan laporan bulanan untuk kelas ini dengan melampirkan minimal 3 foto kegiatan.</p>
                        </div>
                        <a href="{{ route('kelas.laporan.create', [$kelompok, $kelas]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Buat Laporan Pertama</span>
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
