{{-- Section Kegiatan Khusus / Insidental Asrama --}}
@if($khususList->count() > 0)
    <div
        x-show="activeDayTab === 'all' || activeDayTab === 'khusus'"
        class="space-y-3 pt-4 border-t border-slate-200/80 dark:border-slate-800"
    >
        <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full bg-purple-600"></span>
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                    Agenda Khusus / Insidental Asrama
                </h3>
            </div>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                {{ $khususList->count() }} Kegiatan
            </span>
        </div>

        <div class="space-y-2.5">
            @foreach($khususList as $kg)
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-2.5 transition hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="space-y-1.5 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $kg->jenis->badgeColor() }}">
                                    <span class="size-1.5 rounded-full {{ $kg->jenis->dotColor() }}"></span>
                                    {{ $kg->jenis->label() }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-purple-50 dark:bg-slate-800 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-slate-700">
                                    <svg class="size-3 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $kg->tanggal_mulai ? $kg->tanggal_mulai->translatedFormat('l, d F Y') : '-' }}</span>
                                </span>
                                @if($kg->jam_mulai)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        <svg class="size-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ substr($kg->jam_mulai, 0, 5) }} - {{ $kg->jam_selesai ? substr($kg->jam_selesai, 0, 5) : 'Selesai' }} WIB</span>
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
                                <form
                                    method="POST"
                                    action="{{ route('kegiatan.destroy', $kg) }}"
                                    class="inline"
                                    data-confirm="Apakah Anda yakin ingin menghapus kegiatan '{{ $kg->nama }}'?"
                                    data-confirm-title="Hapus Kegiatan Khusus"
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
            @endforeach
        </div>
    </div>
@endif
