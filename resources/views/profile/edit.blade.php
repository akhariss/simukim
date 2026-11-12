<x-app-layout title="Profil Pengguna — {{ $user->name }}">
    @php
        $santri = $user->santri;
        $kamar = $santri?->penempatanAktif?->kamar;
        $gedung = $kamar?->lantai?->gedung;
        $roleName = $user->roles->first()?->name ?? 'mahasantri';
        $roleLabels = [
            'admin' => ['label' => 'Super Administrator', 'color' => 'bg-purple-50 text-purple-700 dark:bg-slate-800 dark:text-purple-300 border-purple-200/80'],
            'tutor' => ['label' => 'Tutor Pengajar', 'color' => 'bg-indigo-50 text-indigo-700 dark:bg-slate-800 dark:text-indigo-300 border-indigo-200/80'],
            'mudabbir' => ['label' => 'Mudabbir Pembina', 'color' => 'bg-amber-50 text-amber-700 dark:bg-slate-800 dark:text-amber-300 border-amber-200/80'],
            'mahasantri' => ['label' => 'Mahasantri Asrama', 'color' => 'bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-blue-300 border-blue-200/80'],
        ];
        $roleInfo = $roleLabels[$roleName] ?? $roleLabels['mahasantri'];
        $wa = $santri?->wa_santri ?? $santri?->no_hp;
        $waUrl = $wa ? ('https://wa.me/' . preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $wa))) : null;
    @endphp

    <div class="space-y-4 sm:space-y-6 max-w-4xl">
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Profil Saya']
            ]"
            title="Profil Pengguna"
            description="{{ $user->auth_source === 'campus' ? 'Informasi identitas resmi dan status kepesertaan asrama Anda.' : 'Kelola informasi akun lokal dan kata sandi Anda.' }}"
        />

        @if($user->auth_source === 'campus')
            <!-- 1. HERO BANNER IDENTITAS KAMPUS (VIEW ONLY) -->
            <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-start sm:items-center justify-between flex-col sm:flex-row gap-4">
                    <div class="flex items-center gap-4">
                        <div class="size-16 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-2xl font-mono shadow-md shrink-0 select-none">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100">{{ $user->name }}</h1>
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase border {{ $roleInfo['color'] }}">
                                    {{ $roleInfo['label'] }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200/60">
                                    ✓ Aktif
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 font-mono mt-1 flex items-center gap-2 flex-wrap">
                                <span>NIM: <strong class="text-slate-800 dark:text-slate-200">{{ $user->nomor_induk ?: '-' }}</strong></span>
                                <span class="text-slate-300 dark:text-slate-700">•</span>
                                <span>{{ $user->email }}</span>
                                <span class="text-slate-300 dark:text-slate-700">•</span>
                                @if($user->auth_source === 'campus')
                                    <span class="text-emerald-600 dark:text-emerald-400 font-sans font-semibold">🌐 Terverifikasi SAHAJA SSO</span>
                                @else
                                    <span class="text-slate-500 dark:text-slate-400 font-sans font-semibold">🔐 Akun Lokal Asrama</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. GRID DETAIL INFORMASI RESMI (VIEW ONLY) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- KARTU AKADEMIK -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>🏛️</span><span>Identitas Akademik Resmi (Siakad)</span>
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Program Studi</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $santri?->prodi ?: 'Umum' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Fakultas / Unit</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $santri?->fakultas ?: 'UIN Siber Syekh Nurjati' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Jenis Kelamin</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                {{ ($santri?->gender?->value ?? $santri?->gender) === 'P' ? 'Perempuan (Akhwat)' : 'Laki-laki (Ikhwan)' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Tahun Angkatan</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $santri?->angkatan ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- KARTU ASRAMA & HUNIAN -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>🚪</span><span>Penempatan Kamar & Asrama</span>
                    </h2>
                    <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                        <div class="col-span-2">
                            <span class="text-slate-400 text-[11px] block">Gedung & Nomor Kamar</span>
                            @if($kamar)
                                <span class="font-bold text-blue-600 dark:text-blue-400 text-sm block">
                                    {{ $gedung?->nama ?? 'Gedung Asrama' }}
                                </span>
                                <span class="text-slate-500 text-[11px]">Lt.{{ $kamar->lantai?->nomor_lantai ?? '1' }} / Kamar {{ $kamar->nomor_kamar }}</span>
                            @else
                                <span class="font-semibold text-amber-600 dark:text-amber-400">Belum Ditempatkan</span>
                                <span class="text-slate-400 text-[11px] block mt-0.5">Penempatan kamar dikelola oleh pengurus asrama.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- KARTU KONTAK -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <span>📱</span><span>Kontak & Komunikasi</span>
                    </h2>
                    <div class="text-xs space-y-2 pt-1">
                        <div>
                            <span class="text-slate-400 text-[11px] block">No. WhatsApp Santri</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $wa ?: '-' }}</span>
                                @if($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">Chat WA &rarr;</a>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Email Resmi Kampus</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ $user->email }}</span>
                        </div>
                    </div>
                </div>

                <!-- KARTU PEMBERITAHUAN SSO KAMPUS -->
                <div class="p-5 rounded-2xl bg-blue-50/60 dark:bg-slate-900 border border-blue-200/80 dark:border-slate-800 shadow-2xs space-y-3 flex flex-col justify-between">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300 flex items-center gap-1.5">
                            <span>🔐</span><span>Autentikasi Terpusat SSO Portal</span>
                        </h2>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mt-2">
                            Profil dan kata sandi Anda disinkronkan langsung dari Portal Resmi UIN Siber Syekh Nurjati. Untuk memperbarui biodata atau mengganti kata sandi, silakan akses portal kampus resmi.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="https://uinssc.ac.id" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition">
                            <span>Buka Website Resmi (uinssc.ac.id)</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- TAMPILAN KHUSUS AKUN LOKAL ASRAMA (BISA EDIT NAMA, EMAIL, PASSWORD) -->
            <div class="p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xs">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
