<section>
    <header class="mb-4 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-4">
            <div class="size-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-xl font-mono shadow-md shrink-0 select-none">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 truncate">{{ $user->name }}</h3>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-blue-300 border border-blue-200/80">
                        {{ $user->roles->first()?->name ?? 'Mahasantri' }}
                    </span>
                    @if($user->auth_source === 'campus')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 border border-emerald-200">
                            🌐 Terverifikasi SAHAJA SSO
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200">
                            🔐 Akun Lokal Asrama
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $user->email }}</p>
            </div>
        </div>

        <!-- RINGKASAN AKADEMIK & ASRAMA -->
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs bg-slate-50 dark:bg-slate-800/40 p-3 rounded-xl border border-slate-200/60 dark:border-slate-800">
            <div>
                <span class="text-slate-400 text-[10px] block">NIM / NIP</span>
                <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $user->nomor_induk ?: '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 text-[10px] block">Program Studi</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300 truncate block">{{ $user->santri?->prodi ?: 'Umum' }}</span>
            </div>
            <div>
                <span class="text-slate-400 text-[10px] block">Kamar Asrama</span>
                <span class="font-semibold {{ $user->santri?->penempatanAktif?->kamar ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }}">
                    {{ $user->santri?->penempatanAktif?->kamar?->nomor_kamar ? ('Kamar ' . $user->santri->penempatanAktif->kamar->nomor_kamar) : 'Belum Ditempatkan' }}
                </span>
            </div>
            <div>
                <span class="text-slate-400 text-[10px] block">Status Keaktifan</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">✓ Aktif</span>
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        @if($user->auth_source === 'campus')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block text-slate-500 dark:text-slate-400 text-[11px] font-bold mb-1">Nama Lengkap (Portal Kampus)</label>
                    <input type="text" value="{{ $user->name }}" disabled class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-slate-500 dark:text-slate-400 text-[11px] font-bold mb-1">Email Resmi (Portal Kampus)</label>
                    <input type="email" value="{{ $user->email }}" disabled class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 cursor-not-allowed font-mono">
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 text-[11px] font-bold mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                    <x-input-error class="mt-1" :messages="$errors->get('name')" />
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 text-[11px] font-bold mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-mono">
                    <x-input-error class="mt-1" :messages="$errors->get('email')" />
                </div>
            </div>
        @endif

        <div>
            <label class="block text-slate-700 dark:text-slate-300 text-[11px] font-bold mb-1">Nomor WhatsApp Aktif</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $user->santri?->no_hp ?? $user->santri?->wa_santri) }}" placeholder="Contoh: 081234567890" class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-mono">
            <span class="text-[10px] text-slate-400 block mt-1">Nomor ini digunakan untuk komunikasi oleh pengurus asrama / Mudabbir.</span>
            <x-input-error class="mt-1" :messages="$errors->get('no_hp')" />
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition cursor-pointer">
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <span>✓</span><span>Profil dan kontak WhatsApp berhasil diperbarui.</span>
                </p>
            @endif
        </div>
    </form>
</section>
