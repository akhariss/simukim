<x-app-layout>
    <x-slot name="title">Kelompok Kajian</x-slot>

    {{-- Header --}}
    <x-page-header
        :breadcrumbs="[
            ['label' => 'Kelompok Kajian']
        ]"
        title="Kelompok Kajian"
        description="Kelompok belajar mahasantri untuk kajian Al-Qur'an, Kitab, dan Masal."
    >
        <x-slot:actions>
            <a href="{{ route('kelompok.export', ['format' => 'excel']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition shadow-2xs">
                <svg class="size-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>
            @hasrole('admin')
                <button type="button" @click="$dispatch('open-modal', 'modal-batch-kelompok')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition shadow-2xs cursor-pointer">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Batch Generate</span>
                </button>
                <button type="button" @click="$dispatch('open-modal', 'modal-tambah-kelompok')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-98 transition shadow-xs cursor-pointer">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Kelompok</span>
                </button>
            @endhasrole
        </x-slot:actions>
    </x-page-header>

    {{-- KPI Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <x-kpi-card
            label="Total Kelompok"
            :value="$stats->total_kelompok ?? 0"
            color="slate"
        />
        <x-kpi-card
            label="Total Kajian"
            :value="$totalKajian ?? 0"
            color="blue"
        />
        <x-kpi-card
            label="Sudah Berkelompok"
            :value="$stats->total_santri_terbagi ?? 0"
            color="emerald"
        />
        <x-kpi-card
            label="Belum Berkelompok"
            :value="$santriTidakBerkelompokCount"
            :color="$santriTidakBerkelompokCount > 0 ? 'rose' : 'slate'"
        />
    </div>

    {{-- Smart Filter & Search --}}
    @include('kelompok.partials.filter-bar')

    {{-- Table & Card List via Atomic Container --}}
    <x-table-container>
        <x-slot:desktop>
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Kelompok</th>
                        <th class="px-4 py-3">Kapasitas Santri</th>
                        <th class="px-4 py-3">Penugasan Tutor</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
                    @forelse($kelompokList as $item)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3">
                                <a href="{{ route('kelompok.show', $item) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400">{{ $item->nama }}</a>
                                @if($item->kode)<p class="text-[10px] font-mono text-slate-400 mt-0.5">{{ $item->kode }}</p>@endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $item->anggota_count }}</span>
                                    <span class="text-slate-400">/ {{ $item->kapasitas }} Santri</span>
                                </div>
                                @php $pct = $item->kapasitas > 0 ? min(100, round(($item->anggota_count / $item->kapasitas) * 100)) : 0; @endphp
                                <div class="w-24 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $pct >= 100 ? 'bg-rose-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width:{{ $pct }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $assigned = $item->pengampu->count();
                                    $target = $totalKajian ?? 0;
                                @endphp
                                @if($target > 0)
                                    @if($assigned >= $target)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                                            <svg class="size-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Lengkap ({{ $assigned }}/{{ $target }} Kajian)</span>
                                        </span>
                                    @elseif($assigned > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                            <span>{{ $assigned }}/{{ $target }} Kajian Terisi</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40">
                                            <span>Belum Ditugaskan</span>
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                        {{ $assigned > 0 ? "{$assigned} Tutor Terisi" : 'Belum Ditugaskan' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1" title="{{ $item->keterangan ?? $item->deskripsi }}">
                                    {{ $item->keterangan ?? $item->deskripsi ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('kelompok.show', $item) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/40 transition">
                                    <span>Detail</span>
                                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                                @if($search !== '' || $filterKapasitas !== '' || $filterTutor !== '')
                                    Tidak ada kelompok kajian yang sesuai dengan filter yang dipilih. <a href="{{ route('kelompok.index') }}" class="text-emerald-600 hover:underline font-semibold ml-1">Reset filter</a>
                                @else
                                    Belum ada kelompok kajian yang terdaftar untuk periode aktif ini.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-slot:desktop>

        <x-slot:mobile>
            <div class="p-3 space-y-2.5">
                @forelse($kelompokList as $item)
                    @php
                        $assigned = $item->pengampu->count();
                        $target = $totalKajian ?? 0;
                    @endphp
                    <div class="p-3.5 rounded-xl bg-slate-50/50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('kelompok.show', $item) }}" class="font-bold text-xs text-slate-900 dark:text-white">{{ $item->nama }}</a>
                                @if($item->kode)<p class="text-[10px] font-mono text-slate-400">{{ $item->kode }}</p>@endif
                            </div>
                            @if($target > 0 && $assigned >= $target)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    Tutor Lengkap
                                </span>
                            @elseif($assigned > 0)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                    {{ $assigned }}/{{ $target }} Tutor
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                                    Belum Ada Tutor
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500">
                            <span>Santri: {{ $item->anggota_count }} / {{ $item->kapasitas }}</span>
                            @if($item->keterangan ?? $item->deskripsi)
                                <span class="text-slate-400 truncate max-w-[150px]">{{ $item->keterangan ?? $item->deskripsi }}</span>
                            @endif
                        </div>
                        <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/60 flex justify-end">
                            <a href="{{ route('kelompok.show', $item) }}" class="text-xs font-bold text-emerald-600 hover:underline">Detail &rarr;</a>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">
                        @if($search !== '' || $filterKapasitas !== '' || $filterTutor !== '')
                            Tidak ada kelompok kajian yang sesuai dengan filter. <a href="{{ route('kelompok.index') }}" class="text-emerald-600 hover:underline font-semibold ml-1">Reset</a>
                        @else
                            Belum ada kelompok kajian yang terdaftar.
                        @endif
                    </div>
                @endforelse
            </div>
        </x-slot:mobile>

        @if($kelompokList->hasPages())
            <x-slot:pagination>
                {{ $kelompokList->links() }}
            </x-slot:pagination>
        @endif
    </x-table-container>

    @hasrole('admin')
        @include('kelompok.partials.modal-tambah-kelompok')
        @include('kelompok.partials.modal-batch-kelompok')
    @endhasrole
</x-app-layout>
