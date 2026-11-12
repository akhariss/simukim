<x-app-layout>
    <x-slot name="title">Master Kajian & Jadwal Sesi</x-slot>

    <!-- Header Halaman Identik dengan Menu Lain -->
    <x-page-header
        :breadcrumbs="[
            ['label' => 'Master Kajian']
        ]"
        title="Master Kajian"
        description="Kurikulum kajian (Al-Qur'an, Kitab, dan Masal) serta jadwal sesi pertemuannya untuk periode aktif."
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('kelas.master.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition active:scale-98 cursor-pointer"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Kajian</span>
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- Cards List Master Kelas & Jadwal Sesi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($kelasList as $kelas)
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $kelas->tipe->badgeClass() }}">
                                {{ $kelas->tipe->label() }}
                            </span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $kelas->nama }}</h2>
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $kelas->is_active ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-400' }}">
                            {{ $kelas->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    @if($kelas->deskripsi)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">{{ $kelas->deskripsi }}</p>
                    @endif

                    {{-- Jadwal Sesi Pertemuan List --}}
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                Jadwal Sesi ({{ $kelas->jadwal->count() }} Pertemuan)
                            </span>
                            <button
                                type="button"
                                @click="$dispatch('open-modal', 'modal-tambah-jadwal-{{ $kelas->id }}')"
                                class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline"
                            >
                                + Tambah Sesi
                            </button>
                        </div>

                        <div class="space-y-1.5">
                            @forelse($kelas->jadwal as $jadwal)
                                <div class="p-2.5 rounded-xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between gap-2 text-xs">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ $jadwal->nama_sesi }}</p>
                                        <p class="text-[11px] text-slate-500 font-medium">
                                            {{ $jadwal->hari }} &bull; {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                                            @if($jadwal->ruangan) &bull; {{ $jadwal->ruangan }} @endif
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        data-confirm="Hapus sesi {{ $jadwal->nama_sesi }} ({{ $jadwal->hari }})?"
                                        data-action="{{ route('kelas.master.jadwal.destroy', $jadwal) }}"
                                        data-method="DELETE"
                                        class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition confirm-trigger"
                                        title="Hapus sesi"
                                    >
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 italic py-3 text-center bg-slate-50/50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                                    Belum ada sesi jadwal pertemuan.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                    <button
                        type="button"
                        data-confirm="Hapus kajian {{ $kelas->nama }} beserta seluruh jadwal sesinya?"
                        data-action="{{ route('kelas.master.destroy', $kelas) }}"
                        data-method="DELETE"
                        class="text-rose-600 hover:text-rose-700 font-semibold confirm-trigger"
                    >
                        Hapus Kajian
                    </button>
                    <a
                        href="{{ route('kelas.master.edit', $kelas) }}"
                        class="font-semibold text-blue-600 dark:text-blue-400 hover:underline"
                    >
                        Edit Kajian
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 p-8 text-center text-xs text-slate-400 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800/80">
                Belum ada master kajian untuk periode aktif {{ $periode->nama ?? '' }}. Silakan klik <strong>Tambah Kajian</strong> untuk mendaftarkan kajian baru beserta jadwal pertemuannya.
            </div>
        @endforelse
    </div>

    {{-- Modals --}}
    @include('kelas.master.partials.modals-kelasList')
</x-app-layout>
