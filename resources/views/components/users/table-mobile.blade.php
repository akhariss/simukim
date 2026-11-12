<!-- TAMPILAN KHUSUS MOBILE (< md): STACKED CARDS (HARAM HORIZONTAL SCROLL) -->
<div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800">
    @forelse($users as $index => $u)
        @php
            $roleName = $u->roles->first()?->name ?? 'mahasantri';
            $santri = $u->santri;
            $gender = $santri?->gender instanceof \App\Enums\Gender ? $santri->gender->value : (string) ($santri?->gender ?? '-');
            $santriStatus = $santri?->status instanceof \App\Enums\SantriStatus ? $santri->status->value : (string) $santri?->status;
            $penempatan = $santri?->penempatanAktif;
            $kamar = $penempatan?->kamar;
            $lantai = $kamar?->lantai;
            $gedung = $lantai?->gedung;
            $rowNum = $users->firstItem() + $index;
        @endphp
        <div class="p-3.5 space-y-2.5">
            <!-- Baris 1: Nama, Role, dan Status -->
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="size-9 rounded-2xl bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-200/70 dark:border-blue-800">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-mono text-[10px] text-slate-400 font-bold">#{{ $rowNum }}</span>
                            <span class="font-bold text-sm text-slate-900 dark:text-white leading-tight">
                                {{ $u->name }}
                            </span>
                            @if($roleName === 'admin')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-800 border border-purple-200/80 dark:bg-slate-800 dark:text-purple-300 dark:border-slate-700">Admin</span>
                            @elseif($roleName === 'tutor')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200/80 dark:bg-slate-800 dark:text-indigo-300 dark:border-slate-700">Tutor</span>
                            @elseif($roleName === 'mudabbir')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-slate-800 dark:text-amber-300 dark:border-slate-700">Mudabbir</span>
                            @else
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200/80 dark:bg-slate-800 dark:text-blue-300 dark:border-slate-700">Santri</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $u->email }}</div>
                    </div>
                </div>

                @if($santriStatus === 'aktif')
                    <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/60 dark:bg-slate-800 dark:text-emerald-300 dark:border-slate-700">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                @elseif($santriStatus === 'nonaktif')
                    <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200/60 dark:bg-slate-800 dark:text-rose-300 dark:border-slate-700">
                        Nonaktif
                    </span>
                @endif
            </div>

            <!-- Baris 2: Detail Identitas & Kamar/Halaqah -->
            <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-600 dark:text-slate-300 border border-slate-100 dark:border-slate-800">
                <div>
                    <span class="text-[10px] text-slate-400 block font-semibold">Nomor Identitas</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $u->nomor_induk ?: '-' }}</span>
                    @if($santri)
                        @if($gender === 'L')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200/60 dark:bg-slate-800 dark:text-blue-300 dark:border-slate-700 ml-1">Ikhwan</span>
                        @elseif($gender === 'P')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-pink-50 text-pink-800 border border-pink-200/60 dark:bg-slate-800 dark:text-pink-300 dark:border-slate-700 ml-1">Akhwat</span>
                        @endif
                    @endif
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block font-semibold">Penempatan / Lokasi</span>
                    @if($kamar)
                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate block">
                            {{ $gedung?->nama ?? 'Gedung' }} (K.{{ $kamar->nomor_kamar }})
                        </span>
                    @elseif($roleName === 'tutor')
                        <span class="text-slate-700 dark:text-slate-300 truncate block">
                            {{ $u->halaqahBinaan?->pluck('nama_halaqah')?->first() ?: ($u->kelasTutor?->pluck('nama_kelas')?->first() ?: 'Belum ada kelas') }}
                        </span>
                    @else
                        <span class="text-slate-400 italic">Belum ditempatkan</span>
                    @endif
                </div>

                @if($santri && $santri->prodi)
                    <div class="col-span-2 pt-1 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 truncate">{{ $santri->prodi }} ({{ $santri->angkatan ?: '-' }})</span>
                        @if($santri->no_hp)
                            <span class="font-mono text-slate-600 dark:text-slate-400">{{ $santri->no_hp }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Baris 3: Aksi Mobile -->
            <div class="flex items-center justify-end gap-1.5 pt-1">
                <a
                    href="{{ route('users.show', $u->id) }}"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-slate-800 border border-emerald-200/80 dark:border-slate-700 hover:bg-emerald-100 dark:hover:bg-slate-700 transition"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Detail
                </a>

                <a
                    href="{{ route('users.edit', $u->id) }}"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-100"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit
                </a>

                @if($u->id !== auth()->id())
                    <form
                        action="{{ route('users.destroy', $u->id) }}"
                        method="POST"
                        class="inline"
                        data-confirm="Hapus pengguna '{{ $u->name }}' secara permanen dari sistem?"
                        data-confirm-title="Hapus Pengguna"
                        data-confirm-btn="Ya, Hapus Pengguna"
                        data-confirm-type="danger"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl text-rose-600 dark:text-rose-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-slate-700 hover:border-rose-200 dark:hover:border-rose-800 transition cursor-pointer"
                        >
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-xs text-slate-400">
            Tidak ada pengguna yang cocok dengan kriteria pencarian.
        </div>
    @endforelse
</div>
