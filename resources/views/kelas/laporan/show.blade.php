<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Laporan Bulanan', 'url' => route('kelas.laporan.index')],
                ['label' => 'Detail Laporan']
            ]"
            :title="'Laporan: ' . $laporan->nama_bulan . ' ' . $laporan->tahun"
            :description="'Tutor: ' . ($laporan->tutor?->name ?? '—') . ' | Kelompok: ' . ($laporan->kelompok?->nama ?? '—') . ' | Kajian: ' . ($laporan->kelas?->nama ?? '—')"
        >
            <x-slot:actions>
                <a href="{{ route('kelas.laporan.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Isi Laporan --}}
        <div class="lg:col-span-2 space-y-4">
            <x-card>
                <div class="flex items-start justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Catatan Laporan</h3>
                    <x-badge :class="$laporan->status->badgeClass()">{{ $laporan->status->label() }}</x-badge>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $laporan->catatan }}</p>
            </x-card>

            @if($laporan->foto->isNotEmpty())
                <x-card>
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Foto Dokumentasi Kegiatan</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($laporan->foto as $foto)
                            @php
                                $secureUrl = route('secure.file', ['category' => 'laporan-kelas', 'filename' => basename($foto->file_path)]);
                            @endphp
                            <a href="{{ $secureUrl }}" target="_blank"
                               class="block rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800 hover:opacity-90 transition-opacity">
                                <img src="{{ $secureUrl }}"
                                     alt="{{ $foto->caption ?? $foto->file_name }}"
                                     class="w-full h-32 object-cover">
                                @if($foto->caption)
                                    <p class="px-2 py-1 text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ $foto->caption }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        {{-- Panel Review --}}
        <div class="space-y-4">
            <x-card>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Informasi Pelaksanaan</h3>
                <dl class="space-y-2 text-xs">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-400">Tutor Pengampu</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-200 text-right">{{ $laporan->tutor?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-400">Kelompok Santri</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-200 text-right">{{ $laporan->kelompok?->nama ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-400">Kajian</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-200 text-right">{{ $laporan->kelas?->nama ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-400">Bulan / Tahun</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-200">{{ $laporan->nama_bulan }} {{ $laporan->tahun }}</dd>
                    </div>
                    @if($laporan->reviewed_at)
                        <div class="flex justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <dt class="text-slate-400">Direview oleh</dt>
                            <dd class="text-slate-600 dark:text-slate-300 text-right">{{ $laporan->reviewer?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-400">Tgl Review</dt>
                            <dd class="text-slate-600 dark:text-slate-300">{{ $laporan->reviewed_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            @hasrole('admin')
                <x-card>
                    <div class="flex items-center justify-between mb-3.5">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Verifikasi Admin</h3>
                        <span class="text-[11px] font-semibold text-slate-400">Keputusan Review</span>
                    </div>

                    <form action="{{ route('kelas.laporan.status', $laporan) }}" method="POST"
                          x-data="{ status: '{{ $laporan->status === \App\Enums\LaporanStatus::Direview ? 'direview' : 'selesai' }}' }"
                          class="space-y-3.5 text-xs">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" :value="status">

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-2">Status Verifikasi</label>
                            <div class="grid grid-cols-2 gap-2.5">
                                {{-- Pilihan 1: Disetujui (Selesai) --}}
                                <button type="button"
                                        @click="status = 'selesai'"
                                        class="flex flex-col items-start p-3 rounded-xl border text-left transition-all cursor-pointer"
                                        :class="status === 'selesai'
                                            ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-100 ring-2 ring-emerald-500/20'
                                            : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div class="flex items-center gap-1.5 font-bold text-xs" :class="status === 'selesai' ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-700 dark:text-slate-300'">
                                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Disetujui</span>
                                    </div>
                                    <span class="text-[10px] mt-1 text-slate-500 dark:text-slate-400 leading-tight">Laporan sah & selesai</span>
                                </button>

                                {{-- Pilihan 2: Perlu Revisi --}}
                                <button type="button"
                                        @click="status = 'direview'"
                                        class="flex flex-col items-start p-3 rounded-xl border text-left transition-all cursor-pointer"
                                        :class="status === 'direview'
                                            ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 text-amber-900 dark:text-amber-100 ring-2 ring-amber-500/20'
                                            : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div class="flex items-center gap-1.5 font-bold text-xs" :class="status === 'direview' ? 'text-amber-700 dark:text-amber-300' : 'text-slate-700 dark:text-slate-300'">
                                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Perlu Revisi</span>
                                    </div>
                                    <span class="text-[10px] mt-1 text-slate-500 dark:text-slate-400 leading-tight">Perlu perbaikan tutor</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="catatan_admin" value="Catatan Review (opsional)" />
                            <textarea name="catatan_admin" id="catatan_admin" rows="3"
                                      class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 text-xs px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500/20"
                                      placeholder="Koreksi atau catatan arahan revisi untuk tutor...">{{ $laporan->catatan_admin }}</textarea>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition cursor-pointer shadow-2xs">
                                Simpan Verifikasi
                            </button>
                        </div>
                    </form>
                </x-card>
            @endhasrole
        </div>
    </div>
</x-app-layout>
