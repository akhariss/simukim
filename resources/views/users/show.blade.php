<x-app-layout title="Detail Pengguna — {{ $user->name }}">
    @php
        $santri = $user->santri;
        $kamar = $santri?->penempatanAktif?->kamar;
        $gedung = $kamar?->lantai?->gedung;
        $roleName = $user->roles->first()?->name ?? 'mahasantri';
        $roleLabels = [
            'admin' => ['label' => 'Administrator', 'color' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border-purple-200/80'],
            'tutor' => ['label' => 'Tutor Pengajar', 'color' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200/80'],
            'mudabbir' => ['label' => 'Mudabbir Pembina', 'color' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-200/80'],
            'mahasantri' => ['label' => 'Mahasantri Asrama', 'color' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-200/80'],
        ];
        $roleInfo = $roleLabels[$roleName] ?? $roleLabels['mahasantri'];
        $wa = $santri?->wa_santri ?? $santri?->no_hp;
        $waUrl = $wa ? ('https://wa.me/' . preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $wa))) : null;
    @endphp

    <div class="space-y-4 sm:space-y-6">
        <!-- 1. MODULAR PAGE HEADER -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kelola Pengguna', 'url' => route('users.index')],
                ['label' => $user->name]
            ]"
            title="{{ $user->name }}"
            description="Detail informasi akun, identitas sivitas kampus, kontak, dan riwayat peran pengguna."
        >
            <x-slot:actions>
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Daftar</span>
                </a>
                <a href="{{ route('users.edit', $user->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 transition shadow-xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Pengguna</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        <!-- 2. PROFIL HERO BANNER -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
            <div class="flex items-center gap-4">
                <div class="size-14 sm:size-16 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-bold text-2xl font-mono shadow-md shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100">{{ $user->name }}</h1>
                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase border {{ $roleInfo['color'] }}">
                            {{ $roleInfo['label'] }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200/60">
                            ✓ Aktif
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 font-mono mt-1 flex items-center gap-2 flex-wrap">
                        <span>{{ $roleName === 'tutor' || $roleName === 'admin' ? 'NIP / ID' : 'NIM' }}: <strong class="text-slate-800 dark:text-slate-200">{{ $user->nomor_induk ?: '-' }}</strong></span>
                        <span>•</span><span>{{ $user->email }}</span>
                        <span>•</span>
                        @if($user->auth_source === 'campus')
                            <span class="text-indigo-600 dark:text-indigo-400 font-sans font-semibold">🌐 Terverifikasi SAHAJA SSO</span>
                        @else
                            <span class="text-slate-500 dark:text-slate-400 font-sans font-semibold">🔐 Akun Lokal Asrama</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. DETAIL KHUSUS BERDASARKAN ROLE -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">

            <!-- KASUS A: ROLE ADMINISTRATOR -->
            @if($roleName === 'admin')
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400 flex items-center gap-1.5">
                        <span>🛡️</span><span>Identitas & Otoritas Administrator</span>
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                        <div><span class="text-slate-400 text-[11px] block">Nama Lengkap</span><strong class="text-slate-800 dark:text-slate-200">{{ $user->name }}</strong></div>
                        <div><span class="text-slate-400 text-[11px] block">NIP / ID Pengelola</span><span class="font-mono text-slate-800 dark:text-slate-200 font-semibold">{{ $user->nomor_induk ?: '-' }}</span></div>
                        <div><span class="text-slate-400 text-[11px] block">Email Resmi</span><span class="font-mono text-slate-800 dark:text-slate-200">{{ $user->email }}</span></div>
                        <div><span class="text-slate-400 text-[11px] block">Tingkat Hak Akses</span><span class="font-bold text-purple-700 dark:text-purple-300">Super Administrator</span></div>
                    </div>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>📋</span><span>Cakupan Wewenang Asrama</span>
                    </h2>
                    <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 pt-1">
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span><span>Kelola Gedung, Lantai, Kamar, & Penempatan Santri</span></li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span><span>Plotting Tutor Pengajar Kelas & Halaqah Binaan</span></li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span><span>Pusat Sinkronisasi & Rekonsiliasi Pangkalan Data SAHAJA</span></li>
                    </ul>
                </div>

            <!-- KASUS B: ROLE TUTOR PENGAJAR -->
            @elseif($roleName === 'tutor')
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                        <span>👨‍🏫</span><span>Profil Akademik Tutor</span>
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                        <div><span class="text-slate-400 text-[11px] block">Nama Lengkap & Gelar</span><strong class="text-slate-800 dark:text-slate-200">{{ $user->name }}</strong></div>
                        <div><span class="text-slate-400 text-[11px] block">NIP / NIK Dosen</span><span class="font-mono text-slate-800 dark:text-slate-200 font-semibold">{{ $user->nomor_induk ?: '-' }}</span></div>
                        <div><span class="text-slate-400 text-[11px] block">Email Resmi</span><span class="font-mono text-slate-800 dark:text-slate-200">{{ $user->email }}</span></div>
                        <div><span class="text-slate-400 text-[11px] block">WhatsApp</span><span class="font-mono text-slate-800 dark:text-slate-200">{{ $santri?->no_hp ?: '-' }}</span></div>
                    </div>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>📚</span><span>Penugasan Kelas & Halaqah Binaan</span>
                    </h2>
                    <div class="space-y-2 text-xs pt-1">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Kelas Kajian Diampu</span>
                            @if($user->kelasTutor->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-1">
                                    @foreach($user->kelasTutor as $k)
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-semibold">{{ $k->nama_kelas }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-500">Belum ditugaskan pada kelas kajian.</span>
                            @endif
                        </div>
                    </div>
                </div>

            <!-- KASUS C: ROLE MAHASANTRI / MUDABBIR -->
            @else
                <!-- Identitas Akademik Santri -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>🏛️</span><span>Informasi Akademik & Kampus</span>
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                        <div><span class="text-slate-400 text-[11px] block">Program Studi</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ $santri?->prodi ?: '-' }}</span></div>
                        <div><span class="text-slate-400 text-[11px] block">Fakultas</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ $santri?->fakultas ?: 'UIN Siber Cirebon' }}</span></div>
                        <div><span class="text-slate-400 text-[11px] block">NIM Santri</span><span class="font-semibold text-slate-800 dark:text-slate-200 font-mono">{{ $user->nomor_induk ?: '-' }}</span></div>
                        <div><span class="text-slate-400 text-[11px] block">Jenis Kelamin</span><span class="font-semibold text-slate-800 dark:text-slate-200">{{ ($santri?->gender?->value ?? $santri?->gender) === 'P' ? 'Perempuan (Akhwat)' : 'Laki-laki (Ikhwan)' }}</span></div>
                    </div>
                </div>

                <!-- Penempatan Kamar -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>🚪</span><span>Penempatan Kamar & Asrama</span>
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Gedung & Nomor Kamar</span>
                            @if($kamar)
                                <span class="font-bold text-blue-600 dark:text-blue-400 text-sm">{{ $gedung?->nama ?? 'Gedung Asrama' }}</span>
                                <span class="text-slate-500 text-[11px] block">Lt.{{ $kamar->lantai?->nomor_lantai ?? '1' }} / Kamar {{ $kamar->nomor_kamar }}</span>
                            @else
                                <span class="font-semibold text-amber-600 dark:text-amber-400">Belum Ditempatkan</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Tanggal Masuk</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                {{ $santri?->penempatanAktif?->tanggal_masuk ? \Carbon\Carbon::parse($santri->penempatanAktif->tanggal_masuk)->translatedFormat('d F Y') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Kontak Santri -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>📱</span><span>Kontak & Domisili Asal</span>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                        <div>
                            <span class="text-slate-400 text-[11px] block">No. WhatsApp</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $wa ?: '-' }}</span>
                                @if($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">Chat WA &rarr;</a>
                                @endif
                            </div>
                        </div>
                        <div><span class="text-slate-400 text-[11px] block">Email Resmi</span><span class="font-mono text-slate-700 dark:text-slate-300 truncate block">{{ $user->email }}</span></div>
                        <div class="sm:col-span-2 pt-1 border-t border-slate-100 dark:border-slate-800"><span class="text-slate-400 text-[11px] block">Alamat Asal</span><span class="text-slate-700 dark:text-slate-300">{{ $santri?->alamat ?: 'Data alamat belum dilengkapi.' }}</span></div>
                    </div>
                </div>

                @if($roleName === 'mahasantri')
                    <!-- Kontak Orang Tua Khusus Mahasantri -->
                    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <span>👨‍👩‍👧</span><span>Kontak Orang Tua / Wali</span>
                        </h2>
                        <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                            <div><span class="text-slate-400 text-[11px] block">Ayah</span><span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $santri?->nama_ayah ?: '-' }}</span><span class="font-mono text-slate-600 dark:text-slate-400 text-[11px] block">{{ $santri?->hp_ayah ?: '' }}</span></div>
                            <div><span class="text-slate-400 text-[11px] block">Ibu / Wali</span><span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $santri?->nama_ibu ?: ($santri?->nama_wali ?: '-') }}</span><span class="font-mono text-slate-600 dark:text-slate-400 text-[11px] block">{{ $santri?->hp_ibu ?: ($santri?->no_hp_wali ?: '') }}</span></div>
                        </div>
                    </div>

                    <!-- Tagihan BRIVA Khusus Mahasantri -->
                    @if(!empty($santri?->briva_number))
                        <div class="col-span-1 lg:col-span-2 p-5 rounded-2xl bg-blue-50/60 dark:bg-slate-900 border border-blue-200/80 dark:border-slate-800 shadow-2xs space-y-3">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300 flex items-center gap-1.5"><span>💳</span><span>Integrasi Billing SAHAJA</span></h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                                <div><span class="text-slate-500 text-[11px] block">Nomor VA BRIVA</span><span class="font-mono font-extrabold text-blue-700 dark:text-blue-300 text-base">{{ $santri->briva_number }}</span></div>
                                <div><span class="text-slate-500 text-[11px] block">Status Tagihan</span><span class="inline-flex mt-1 px-3 py-1 rounded-lg text-xs font-bold {{ strtolower((string)$santri->status_pembayaran) === 'lunas' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">{{ $santri->status_pembayaran ?: 'Belum Bayar' }}</span></div>
                                <div><span class="text-slate-500 text-[11px] block">Nominal</span><span class="font-bold text-slate-800 dark:text-slate-200 text-sm block mt-1">Rp {{ number_format($santri->nominal_tagihan ?: 1250000, 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                    @endif
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
