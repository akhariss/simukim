<x-guest-layout title="Masuk — SIMUKIM — Ma'had Al-Jami'ah">
    <div
        class="min-h-screen min-h-[100dvh] relative flex flex-col justify-between font-sans select-none overflow-x-hidden"
        x-data="{
            showPass: false,
            loginId: @js((string) old('username', old('login_id', old('email', '')))),
            loginPassword: '',
            isSubmitting: false,
            showDemoAccounts: false,
            fastLogin(id, pass) {
                if (this.isSubmitting) return;
                this.isSubmitting = true;
                this.loginId = id;
                this.loginPassword = pass;
                const elUser = document.getElementById('username');
                const elId = document.getElementById('login_id');
                const elPass = document.getElementById('password');
                if (elUser) elUser.value = id;
                if (elId) elId.value = id;
                if (elPass) elPass.value = pass;
                this.$nextTick(() => {
                    if (this.$refs.loginForm) {
                        this.$refs.loginForm.submit();
                    }
                });
            }
        }"
    >
        <!-- Optimized Campus Background (83% Lighter WebP + Progressive JPG, Zero Freeze) -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <picture class="w-full h-full">
                <source srcset="{{ secure_asset('images/mahad-bg.webp') }}" type="image/webp">
                <img
                    src="{{ secure_asset('images/mahad-bg.jpg') }}"
                    alt="Kampus Ma'had Al-Jami'ah"
                    class="w-full h-full object-cover object-center filter brightness-95"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                >
            </picture>
            <!-- Soft Cinematic Dark Overlay (Keeps Campus Architecture Visible, Protects Contrast) -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/20 to-black/70"></div>
        </div>

        <!-- Top Header Navigation -->
        <header class="relative z-10 w-full px-4 sm:px-8 pt-5 pb-2 flex items-center justify-between">
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-slate-900/80 hover:bg-slate-900 border border-white/20 transition shadow-sm group cursor-pointer"
            >
                <svg class="size-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Beranda</span>
            </a>

            <!-- Theme Toggle on Auth Screen -->
            <button
                type="button"
                class="inline-flex items-center justify-center size-9 rounded-xl text-white bg-slate-900/80 hover:bg-slate-900 border border-white/20 transition shadow-sm cursor-pointer"
                x-data="{ currentTheme: window.getCurrentTheme ? window.getCurrentTheme() : 'light' }"
                @theme-changed.window="currentTheme = $event.detail.theme"
                :title="currentTheme === 'dark' ? 'Mode Terang' : 'Mode Gelap'"
                @click="currentTheme = window.toggleTheme()"
            >
                <svg x-show="currentTheme === 'dark'" x-cloak class="size-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg x-show="currentTheme !== 'dark'" class="size-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
        </header>

        <!-- Center Auth Card (100% Solid Opaque, Anti-Transparent, High-Contrast & Professional) -->
        <main class="relative z-10 w-full max-w-[420px] mx-auto px-4 py-4 sm:py-6 flex-1 flex flex-col justify-center">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-white/70 dark:border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] p-7 sm:p-9 space-y-6">
                
                <!-- Brand Header -->
                <div class="text-center space-y-3">
                    <div class="inline-flex items-center justify-center p-3 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 ring-1 ring-slate-900/5">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Ma'had" class="h-12 w-12 object-contain">
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Masuk ke SIMUKIM
                        </h1>
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1">
                            Sistem Informasi Mahasantri Mukim &bull; Ma'had Al-Jami'ah
                        </p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">
                            UIN Siber Syekh Nurjati Cirebon
                        </p>
                    </div>
                </div>

                <!-- Session Flash Message -->
                @if (session('status'))
                    <div class="p-3 rounded-xl text-xs bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                        <svg class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Error Alert Box -->
                @if ($errors->any() || session('error'))
                    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-200 text-xs space-y-1">
                        <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-300">
                            <svg class="size-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Gagal Masuk</span>
                        </div>
                        @if(session('error')) <div>{{ session('error') }}</div> @endif
                        @foreach (array_unique($errors->all()) as $err)
                            <div class="text-[11px] opacity-90">&bull; {{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Login Form -->
                <form x-ref="loginForm" method="POST" action="/login" autocomplete="off" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;" class="space-y-4">
                    @csrf
                    <input type="hidden" name="login_id" :value="loginId">

                    <!-- Username / NIM / NIP -->
                    <div class="space-y-1.5 text-left">
                        <label for="username" class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                            Nomor Induk (NIM / NIP)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input
                                id="username"
                                type="text"
                                name="username"
                                x-model="loginId"
                                placeholder="Contoh: 2388010004 atau NIP-ADM01"
                                class="w-full text-xs rounded-xl pl-10 pr-3.5 py-3 border border-slate-300 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-4 focus:ring-blue-500/15 focus:border-blue-600 transition outline-none font-medium"
                                required
                                autofocus
                                autocomplete="username"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5 text-left">
                        <label for="password" class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                            Kata Sandi Portal
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input
                                id="password"
                                :type="showPass ? 'text' : 'password'"
                                name="password"
                                x-model="loginPassword"
                                placeholder="Masukkan kata sandi portal"
                                class="w-full text-xs rounded-xl pl-10 pr-10 py-3 border border-slate-300 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-4 focus:ring-blue-500/15 focus:border-blue-600 transition outline-none font-medium"
                                required
                                autocomplete="current-password"
                            >
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="absolute right-0 top-0 bottom-0 px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition flex items-center justify-center cursor-pointer"
                                tabindex="-1"
                                aria-label="Lihat kata sandi"
                            >
                                <svg x-show="!showPass" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" x-cloak class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        x-ref="submitBtn"
                        type="submit"
                        class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.99] shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 transition-all flex items-center justify-center cursor-pointer mt-2"
                        :class="isSubmitting ? 'pointer-events-none opacity-70' : ''"
                    >
                        <span x-show="!isSubmitting">Masuk ke Portal Asrama</span>
                        <span x-show="isSubmitting" x-cloak>Memproses...</span>
                    </button>

                    <!-- Clean SSO Notice Badge -->
                    <div class="pt-2 text-center">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60">
                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Terhubung langsung dengan SSO SAHAJA Kampus</span>
                        </div>
                    </div>

                    @if(app()->isLocal())
                        <!-- Demo Accounts Shortcut -->
                        @include('auth.partials.login-demo-accounts')
                    @endif
                </form>
            </div>
        </main>

        <!-- Dedicated Footer with Frosted Glass Pill -->
        <footer class="relative z-10 w-full py-4 px-4 text-center">
            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[11px] font-medium text-white/95 bg-slate-900/85 border border-white/15 shadow-sm">
                &copy; {{ date('Y') }} SIMUKIM &bull; Ma'had Al-Jami'ah &bull; UIN Siber Syekh Nurjati Cirebon
            </span>
        </footer>
    </div>
</x-guest-layout>
