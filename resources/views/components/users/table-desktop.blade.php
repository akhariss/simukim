<!-- TAMPILAN DESKTOP & TABLET (md ke atas): TABEL LENGKAP -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-left text-xs border-collapse">
        @include('components.users.table-head')
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-200">
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

                    $tutorAsal = 'Mitra Luar / Lokal';
                    if ($u->auth_source === 'campus') {
                        $tutorAsal = ($u->santri || strlen($u->nomor_induk) <= 9) ? 'Mahasiswa Senior' : 'Dosen Kampus';
                    }
                @endphp
                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors duration-100">
                    <td class="px-4 py-3 text-center text-slate-400 font-mono text-[11px] align-middle">
                        {{ $rowNum }}
                    </td>

                    {{-- 1. TAB KHUSUS: MAHASANTRI --}}
                    @if($currentRole === 'mahasantri')
                        <td class="px-4 py-3 align-middle">
                            <div class="flex items-center gap-2.5">
                                <div class="size-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-200/70 dark:border-blue-800">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 dark:text-slate-100 leading-tight truncate">{{ $u->name }}</div>
                                    <div class="text-[11px] text-slate-400 truncate">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] align-middle">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $u->nomor_induk }}</span>
                            @if($gender === 'L')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200/60 dark:bg-slate-800 dark:text-blue-300 dark:border-slate-700 ml-1">L</span>
                            @elseif($gender === 'P')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-pink-50 text-pink-800 border border-pink-200/60 dark:bg-slate-800 dark:text-pink-300 dark:border-slate-700 ml-1">P</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @if($kamar)
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $gedung?->nama ?? 'Gedung' }}</span>
                                <span class="text-slate-500 text-[11px]">Lt.{{ $lantai?->nomor_lantai ?? '-' }} / K.{{ $kamar->nomor_kamar }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-slate-800 dark:text-amber-300 dark:border-slate-700">
                                    Belum ditempatkan
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div class="font-medium leading-tight text-slate-800 dark:text-slate-200">{{ $santri?->prodi ?: '-' }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5 flex flex-wrap items-center gap-1.5">
                                <span>{{ $santri?->fakultas ?: 'UIN SSC' }}</span>
                                @if($santri?->briva_number)
                                    <span class="font-mono text-[9px] px-1 rounded bg-blue-50 dark:bg-slate-800 text-blue-700 dark:text-blue-300 font-semibold border border-blue-200/50 dark:border-slate-700" title="Virtual Account BRIVA">BRIVA: {{ $santri->briva_number }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center align-middle">
                            @if($santriStatus === 'aktif')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/60 dark:bg-slate-800 dark:text-emerald-300 dark:border-slate-700">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200/60 dark:bg-slate-800 dark:text-rose-300 dark:border-slate-700">Nonaktif</span>
                            @endif
                            @if($santri?->status_pembayaran)
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9.5px] font-semibold {{ strtolower($santri->status_pembayaran) === 'lunas' ? 'bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-slate-800 dark:text-amber-300' }} border border-slate-200 dark:border-slate-700">
                                        {{ $santri->status_pembayaran }}
                                    </span>
                                </div>
                            @endif
                        </td>

                    {{-- 2. TAB KHUSUS: MUDABBIR --}}
                    @elseif($currentRole === 'mudabbir')
                        <td class="px-4 py-3 align-middle">
                            <div class="font-bold text-slate-900 dark:text-slate-100 leading-tight">{{ $u->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] align-middle">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $u->nomor_induk }}</span>
                            @if($gender === 'L')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200/60 dark:bg-slate-800 dark:text-blue-300 dark:border-slate-700 ml-1">L</span>
                            @elseif($gender === 'P')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-pink-50 text-pink-800 border border-pink-200/60 dark:bg-slate-800 dark:text-pink-300 dark:border-slate-700 ml-1">P</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @if($kamar)
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $gedung?->nama ?? 'Gedung' }}</span>
                                <span class="text-slate-500 text-[11px]">K.{{ $kamar->nomor_kamar }}</span>
                            @else
                                <span class="text-amber-600 dark:text-amber-400 text-xs italic">Belum ada kamar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle text-slate-600 dark:text-slate-300">
                            <div>{{ $santri?->prodi ?: '-' }}</div>
                            <div class="text-[11px] text-slate-400">Angkatan {{ $santri?->angkatan ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] align-middle text-slate-600 dark:text-slate-300">
                            {{ $santri?->no_hp ?: '-' }}
                        </td>

                    {{-- 3. TAB KHUSUS: TUTOR --}}
                    @elseif($currentRole === 'tutor')
                        <td class="px-4 py-3 align-middle">
                            <div class="font-bold text-slate-900 dark:text-slate-100 leading-tight">{{ $u->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] align-middle">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $u->nomor_induk }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="px-2 py-0.5 rounded-md text-[10.5px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $tutorAsal }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle text-slate-700 dark:text-slate-200">
                            {{ $u->halaqahBinaan?->pluck('nama_halaqah')?->first() ?: ($u->kelasTutor?->pluck('nama_kelas')?->first() ?: 'Belum ditugaskan') }}
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] align-middle text-slate-600 dark:text-slate-300">
                            {{ $santri?->no_hp ?: ($u->pegawaiCampus?->no_hp ?? '-') }}
                        </td>

                    {{-- 4. TAB KHUSUS: ADMIN --}}
                    @elseif($currentRole === 'admin')
                        <td class="px-4 py-3 align-middle">
                            <div class="font-bold text-slate-900 dark:text-slate-100 leading-tight">{{ $u->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] align-middle">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $u->nomor_induk }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-purple-50 text-purple-800 border border-purple-200/80 dark:bg-slate-800 dark:text-purple-300 dark:border-slate-700">
                                Super Administrator
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs align-middle">
                            {{ $u->created_at->format('d M Y') }}
                        </td>

                    {{-- 5. TAB SEMUA / UNIVERSAL --}}
                    @else
                        <td class="px-4 py-3 align-middle">
                            <div class="flex items-center gap-2.5">
                                <div class="size-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-200/70 dark:border-blue-800">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 dark:text-slate-100 leading-tight truncate">{{ $u->name }}</div>
                                    <div class="text-[11px] text-slate-400 truncate">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] align-middle">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $u->nomor_induk }}</span>
                            @if($santri)
                                @if($gender === 'L')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200/60 dark:bg-slate-800 dark:text-blue-300 dark:border-slate-700 ml-1">L</span>
                                @elseif($gender === 'P')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-pink-50 text-pink-800 border border-pink-200/60 dark:bg-slate-800 dark:text-pink-300 dark:border-slate-700 ml-1">P</span>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @if($roleName === 'admin')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-800 border border-purple-200/80 dark:bg-slate-800 dark:text-purple-300 dark:border-slate-700">Admin</span>
                            @elseif($roleName === 'tutor')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200/80 dark:bg-slate-800 dark:text-indigo-300 dark:border-slate-700">Tutor</span>
                            @elseif($roleName === 'mudabbir')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-slate-800 dark:text-amber-300 dark:border-slate-700">Mudabbir</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200/80 dark:bg-slate-800 dark:text-blue-300 dark:border-slate-700">Santri</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle text-slate-600 dark:text-slate-300">
                            @if($kamar)
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $gedung?->nama ?? '' }}</span>
                                <span class="text-slate-500 text-[11px]">K.{{ $kamar->nomor_kamar }}</span>
                            @elseif($roleName === 'tutor')
                                <span class="text-xs">{{ $u->halaqahBinaan?->pluck('nama_halaqah')?->first() ?: ($u->kelasTutor?->pluck('nama_kelas')?->first() ?: 'Belum ada kelas') }}</span>
                            @else
                                <span class="text-slate-400 text-xs italic">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center align-middle">
                            @if($santriStatus === 'aktif')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/60 dark:bg-slate-800 dark:text-emerald-300 dark:border-slate-700">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @elseif($santriStatus === 'nonaktif')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200/60 dark:bg-slate-800 dark:text-rose-300 dark:border-slate-700">Nonaktif</span>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                    @endif

                    {{-- AKSI: EDIT & DELETE --}}
                    <td class="px-4 py-3 text-right align-middle whitespace-nowrap">
                        @include('components.users.table-row-actions')
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="size-9 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Tidak ada pengguna yang cocok dengan kriteria pencarian</p>
                            <p class="text-xs text-slate-400 mt-0.5">Coba ubah kata kunci atau bersihkan filter di atas.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
