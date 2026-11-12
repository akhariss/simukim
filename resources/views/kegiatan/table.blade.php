{{-- Mode Daftar: Feed Jadwal & Agenda Terpadu (Sangat Nyaman Dibaca di Desktop & HP) --}}


<div x-data="{ activeDayTab: 'all' }" class="space-y-6">
    @include('kegiatan.partials.day-tabs')

    <!-- Feed Jadwal Hari per Hari -->
    <div class="space-y-6">
        @foreach($dayList as $dName)
            @php $eventsThisDay = $scheduleByDay[$dName]; @endphp
            <div
                x-show="activeDayTab === 'all' || activeDayTab === '{{ strtolower($dName) }}'"
                class="space-y-3"
            >
                <!-- Day Section Header -->
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full {{ $eventsThisDay->count() > 0 ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-700' }}"></span>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-100">
                            Hari {{ $dName }}
                        </h3>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                        {{ $eventsThisDay->count() }} Agenda Terjadwal
                    </span>
                </div>

                @if($eventsThisDay->count() > 0)
                    <div class="space-y-2.5">
                        @foreach($eventsThisDay as $entry)
                            @if($entry->is_kelas && $entry->is_grouped)
                                {{-- Card Kelas Paralel Serentak --}}
                                <div x-data="{ expanded: false }" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3 transition hover:shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                        <div class="space-y-1.5 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200/80 dark:border-slate-700">
                                                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                    Kelas Akademik • Paralel
                                                </span>
                                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 dark:bg-slate-800 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-slate-700">
                                                    {{ count($entry->sub_items) }} Kelompok Serentak
                                                </span>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                    <svg class="size-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>{{ $entry->jam_mulai }} - {{ $entry->jam_selesai ?? 'Selesai' }} WIB</span>
                                                </span>
                                            </div>
                                            <h4 class="text-base font-extrabold text-slate-900 dark:text-white">
                                                {{ $entry->nama }}
                                            </h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Sesi pembelajaran yang dibagi menjadi {{ count($entry->sub_items) }} kelompok halaqah kecil yang berjalan serentak.
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="expanded = !expanded"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition cursor-pointer self-start shrink-0"
                                        >
                                            <span x-text="expanded ? 'Tutup Rincian' : 'Lihat {{ count($entry->sub_items) }} Kelompok'"></span>
                                            <svg class="size-3.5 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>

                                    {{-- Sub-items list --}}
                                    <div x-show="expanded" x-collapse x-cloak class="pt-3 border-t border-slate-100 dark:border-slate-800">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($entry->sub_items as $subK)
                                                <div class="p-3 rounded-xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 flex items-center justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <p class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $subK->nama }}</p>
                                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                                            Lokasi: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $subK->lokasi ?? '-' }}</span>
                                                        </p>
                                                    </div>
                                                    <a href="{{ $subK->url ?? route('kelas.master.index') }}" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition shrink-0">
                                                        Detail
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @elseif($entry->is_kelas)
                                {{-- Card Kelas Mandiri / Biasa --}}
                                @php $k = $entry->item; @endphp
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-2.5 transition hover:border-slate-300 dark:hover:border-slate-700">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                        <div class="space-y-1.5 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200/80 dark:border-slate-700">
                                                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                    {{ $k->tipe_label ?? 'Kelas Akademik' }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                    <svg class="size-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>{{ $entry->jam_mulai }} - {{ $entry->jam_selesai ?? 'Selesai' }} WIB</span>
                                                </span>
                                            </div>
                                            <h4 class="text-base font-extrabold text-slate-900 dark:text-white">
                                                {{ $k->nama }}
                                            </h4>
                                            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-600 dark:text-slate-400 pt-0.5">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <svg class="size-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <span class="text-slate-400">Lokasi:</span>
                                                    <strong class="text-slate-800 dark:text-slate-200">{{ $k->lokasi ?? 'Masjid / Asrama' }}</strong>
                                                </span>
                                            </div>
                                            @if($k->deskripsi)
                                                <p class="text-xs text-slate-500 italic pt-1">{{ $k->deskripsi }}</p>
                                            @endif
                                        </div>

                                        <a
                                            href="{{ $k->url ?? route('kelas.master.index') }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition self-start shrink-0"
                                        >
                                            <span>Detail Kelas</span>
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>
                            @else
                                {{-- Card Kegiatan Rutin Asrama --}}
                                @php $kg = $entry->item; @endphp
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-2.5 transition hover:border-slate-300 dark:hover:border-slate-700">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                        <div class="space-y-1.5 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $kg->jenis->badgeColor() }}">
                                                    <span class="size-1.5 rounded-full {{ $kg->jenis->dotColor() }}"></span>
                                                    {{ $kg->jenis->label() }}
                                                </span>
                                                @if($entry->jam_mulai)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                        <svg class="size-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>{{ $entry->jam_mulai }} - {{ $entry->jam_selesai ?? 'Selesai' }} WIB</span>
                                                    </span>
                                                @endif
                                            </div>
                                            <h4 class="text-base font-extrabold text-slate-900 dark:text-white">
                                                {{ $kg->nama }}
                                            </h4>
                                            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-600 dark:text-slate-400 pt-0.5">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <svg class="size-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <span class="text-slate-400">Lokasi:</span>
                                                    <strong class="text-slate-800 dark:text-slate-200">{{ $kg->lokasi ?? '-' }}</strong>
                                                </span>
                                            </div>
                                            @if($kg->deskripsi)
                                                <p class="text-xs text-slate-500 italic pt-1">{{ $kg->deskripsi }}</p>
                                            @endif
                                        </div>

                                        @hasrole('admin')
                                            <div class="flex items-center gap-2 self-start shrink-0">
                                                <form method="POST" action="{{ route('kegiatan.toggle', $kg) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="p-1.5 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition cursor-pointer" title="Toggle Status">
                                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                    </button>
                                                </form>
                                                <form
                                                    method="POST"
                                                    action="{{ route('kegiatan.destroy', $kg) }}"
                                                    class="inline"
                                                    data-confirm="Apakah Anda yakin ingin menghapus kegiatan '{{ $kg->nama }}'?"
                                                    data-confirm-title="Hapus Kegiatan Rutin"
                                                    data-confirm-btn="Ya, Hapus Kegiatan"
                                                    data-confirm-type="danger"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-slate-800 border border-rose-200 dark:border-slate-700 transition cursor-pointer" title="Hapus">
                                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endhasrole
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-900/40 border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
                        Tidak ada agenda kelas maupun kegiatan asrama pada hari {{ $dName }}.
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Section Kegiatan Khusus / Insidental Asrama --}}
        @include('kegiatan.partials.khusus-feed')
    </div>
</div>
