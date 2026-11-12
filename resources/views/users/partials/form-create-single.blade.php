<!-- FORM PENDAFTARAN PENGGUNA BARU (COMPACT & MOBILE-FRIENDLY) -->
<div x-show="activeTab === 'single'" class="space-y-3.5">
    <!-- Slim Info Banner -->
    <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-900/40 text-[11px] text-blue-800 dark:text-blue-300">
        <span>💡</span>
        <span><strong>Pendaftaran Cepat:</strong> Cukup isi NIM/NIP & Nama. Akun langsung aktif, data lengkap otomatis disinkronkan saat login SSO.</span>
    </div>

    @if ($errors->any())
        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-200 text-xs space-y-1">
            <div class="flex items-center gap-1.5 font-bold text-rose-700 dark:text-rose-300">
                <svg class="size-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Mohon periksa data formulir:</span>
            </div>
            @foreach ($errors->all() as $err)
                <div class="text-[11px] opacity-90">&bull; {{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}" class="space-y-3.5 max-w-2xl">
        @csrf

        <!-- 1. PERAN PENGGUNA (COMPACT SEGMENTED PILLS) -->
        <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                Peran Pengguna <span class="text-rose-500">*</span>
            </label>
            <div class="flex flex-wrap gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80">
                <button type="button" @click="role = 'mahasantri'" class="flex-1 min-w-[120px] px-2.5 py-1.5 rounded-lg text-xs font-semibold transition text-center cursor-pointer"
                    :class="role === 'mahasantri' ? 'bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-300 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'">
                    🎓 Mahasantri
                </button>
                <button type="button" @click="role = 'mudabbir'" class="flex-1 min-w-[120px] px-2.5 py-1.5 rounded-lg text-xs font-semibold transition text-center cursor-pointer"
                    :class="role === 'mudabbir' ? 'bg-white dark:bg-slate-900 text-amber-700 dark:text-amber-300 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'">
                    ⭐ Mudabbir
                </button>
                <button type="button" @click="role = 'tutor'" class="flex-1 min-w-[120px] px-2.5 py-1.5 rounded-lg text-xs font-semibold transition text-center cursor-pointer"
                    :class="role === 'tutor' ? 'bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'">
                    👨‍🏫 Tutor
                </button>
                <button type="button" @click="role = 'admin'" class="flex-1 min-w-[120px] px-2.5 py-1.5 rounded-lg text-xs font-semibold transition text-center cursor-pointer"
                    :class="role === 'admin' ? 'bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-300 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'">
                    🛡️ Admin
                </button>
                <input type="hidden" name="role" :value="role">
            </div>
        </div>

        <!-- 2. METODE OTENTIKASI (COMPACT SWITCH) -->
        <div class="flex items-center justify-between flex-wrap gap-2 pt-1 border-t border-slate-200/60 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Tipe Akun:</span>
            <div class="inline-flex items-center gap-1 p-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs">
                <button type="button" @click="authSource = 'campus'" class="px-2.5 py-1 rounded-md transition cursor-pointer font-semibold"
                    :class="authSource === 'campus' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400'">
                    ⚡ SSO Kampus
                </button>
                <button type="button" @click="authSource = 'local'" class="px-2.5 py-1 rounded-md transition cursor-pointer font-semibold"
                    :class="authSource === 'local' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400'">
                    🔑 Akun Lokal
                </button>
                <input type="hidden" name="auth_source" :value="authSource">
            </div>
        </div>

        <!-- 3. GRID FORM INPUTS (2 KOLOM DESKTOP, COMPACT MOBILE) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- NOMOR INDUK + LOOKUP -->
            <div class="space-y-1">
                <label for="nomor_induk" class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                    <span x-text="(role === 'mahasantri' || role === 'mudabbir') ? 'NIM Mahasiswa' : 'NIP / ID Akun'"></span> <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-1.5">
                    <input id="nomor_induk" type="text" name="nomor_induk" x-model="nomorInduk" @keydown.enter.prevent="lookup()" required
                        :placeholder="(role === 'mahasantri' || role === 'mudabbir') ? 'Contoh: 240100001' : 'Contoh: 19850101... atau ADM-01'"
                        class="flex-1 text-xs font-mono rounded-xl px-3 py-2 border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition outline-none">
                    <button type="button" @click="lookup()" :disabled="loading || !nomorInduk.trim()"
                        class="px-2.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 transition border border-slate-300 dark:border-slate-700 flex items-center gap-1 cursor-pointer shrink-0">
                        <span x-show="!loading">🔍 Cek</span>
                        <span x-show="loading" x-cloak class="flex items-center gap-1">
                            <svg class="size-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </span>
                    </button>
                </div>
            </div>

            <!-- NAMA LENGKAP -->
            <div class="space-y-1">
                <label for="name" class="block text-xs font-bold text-slate-800 dark:text-slate-200">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input id="name" type="text" name="name" x-model="name" required placeholder="Contoh: Ahmad Fauzi / Dr. Maimun"
                    class="w-full text-xs rounded-xl px-3 py-2 border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition outline-none">
            </div>

            <!-- STATUS LOOKUP NOTIFICATION (FULL WIDTH ROW IF SHOWN) -->
            <div class="sm:col-span-2" x-show="lookupMsg" x-cloak>
                <div class="p-2 rounded-xl text-[11px] flex items-center gap-1.5"
                    :class="lookupFound ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700'">
                    <span x-text="lookupFound ? '✓' : 'ℹ️'"></span>
                    <span x-text="lookupMsg"></span>
                </div>
            </div>

            <!-- JENIS KELAMIN -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                    Jenis Kelamin <span class="text-rose-500" x-show="role === 'mahasantri' || role === 'mudabbir'">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <label class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer transition select-none text-xs font-medium"
                        :class="gender === 'L' ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-500 text-blue-800 dark:text-blue-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                        <input type="radio" name="gender" value="L" x-model="gender" class="sr-only">
                        <span>👨 Ikhwan</span>
                    </label>
                    <label class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer transition select-none text-xs font-medium"
                        :class="gender === 'P' ? 'bg-pink-50 dark:bg-pink-950/40 border-pink-500 text-pink-800 dark:text-pink-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                        <input type="radio" name="gender" value="P" x-model="gender" class="sr-only">
                        <span>👩 Akhwat</span>
                    </label>
                </div>
            </div>

            <!-- PROGRAM STUDI / JURUSAN (KHUSUS MAHASANTRI & MUDABBIR) -->
            <div class="space-y-1" x-show="role === 'mahasantri' || role === 'mudabbir'">
                <label for="prodi" class="block text-xs font-bold text-slate-800 dark:text-slate-200">Prodi / Jurusan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input id="prodi" type="text" name="prodi" x-model="prodi" placeholder="Contoh: Pendidikan Agama Islam"
                    class="w-full text-xs rounded-xl px-3 py-2 border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition outline-none">
            </div>

            <!-- EMAIL RESMI -->
            <div class="space-y-1" :class="authSource === 'local' ? '' : (role === 'tutor' || role === 'admin' ? 'sm:col-span-2' : '')">
                <label for="email" class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                    Email Resmi <span class="text-slate-400 font-normal" x-show="authSource === 'campus'">(Opsional SSO)</span><span class="text-rose-500" x-show="authSource === 'local'">*</span>
                </label>
                <input id="email" type="email" name="email" x-model="email" :required="authSource === 'local'"
                    :placeholder="nomorInduk ? (nomorInduk + (role === 'mahasantri' || role === 'mudabbir' ? '@student.uinssc.ac.id' : '@uinssc.ac.id')) : 'nama@uinssc.ac.id'"
                    class="w-full text-xs rounded-xl px-3 py-2 border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition outline-none">
            </div>

            <!-- PASSWORD KHUSUS AKUN LOKAL -->
            <div class="space-y-1" x-show="authSource === 'local'" x-cloak>
                <label for="password" class="block text-xs font-bold text-slate-800 dark:text-slate-200">Kata Sandi Lokal <span class="text-slate-400 font-normal">(Kosongkan untuk default: password)</span></label>
                <div class="relative">
                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" placeholder="Kosongkan untuk default: password"
                        class="w-full text-xs rounded-xl pl-3 pr-8 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition outline-none">
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-0 top-0 bottom-0 px-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition flex items-center justify-center cursor-pointer" tabindex="-1">
                        <span x-text="showPassword ? '🙈' : '👁️'" class="text-xs"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- SUBMIT BUTTON -->
        <div class="pt-1">
            <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.99] transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Daftarkan Pengguna ke Ma'had</span>
            </button>
        </div>
    </form>
</div>
