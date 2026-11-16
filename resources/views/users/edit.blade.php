<x-app-layout title="Edit Pengguna — {{ $user->name }}">
    @php
        $santri = $user->santri;
        $roleName = $user->roles->first()?->name ?? 'mahasantri';
    @endphp

    <div class="space-y-4 sm:space-y-6" x-data="{ currentRole: @js((string) old('role', $roleName)) }">
        <!-- 1. MODULAR PAGE HEADER -->
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kelola Pengguna', 'url' => route('users.index')],
                ['label' => 'Edit Pengguna']
            ]"
            :title="'Edit Pengguna: ' . $user->name"
            description="Perbarui informasi profil, kontak, atau peran akun pengguna sistem."
        >
            <x-slot:actions>
                <a href="{{ route('users.show', $user->id) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Detail Pengguna</span>
                </a>
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Daftar Pengguna</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        <!-- 2. FORM EDIT SESUAI ROLE -->
        <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-2xs max-w-2xl">
            <div class="pb-4 mb-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h1 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span>✏️</span><span>Edit Pengguna: {{ $user->name }}</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">Formulir disesuaikan secara dinamis berdasarkan peran pengguna.</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider"
                    :class="{
                        'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300': currentRole === 'admin',
                        'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300': currentRole === 'tutor',
                        'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300': currentRole === 'mudabbir',
                        'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300': currentRole === 'mahasantri'
                    }" x-text="currentRole">
                </span>
            </div>

            <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="space-y-3.5">
                    <!-- Peran / Hak Akses -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Peran / Hak Akses Pengguna <span class="text-rose-500">*</span></label>
                        <select name="role" x-model="currentRole" required class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-semibold">
                            <option value="admin">Administrator (Akses Penuh Manajemen Asrama)</option>
                            <option value="tutor">Tutor Pengajar (Pengampu Kelas / Halaqah)</option>
                            <option value="mudabbir">Mudabbir Pembina (Pembina Kamar / Santri)</option>
                            <option value="mahasantri">Mahasantri Asrama (Santri Mukim)</option>
                        </select>
                    </div>

                    <!-- Identitas Utama -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                <span x-text="currentRole === 'tutor' ? 'Nama Lengkap Beserta Gelar' : 'Nama Lengkap'"></span>
                                <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1" x-text="currentRole === 'admin' ? 'NIP / ID Pengelola' : (currentRole === 'tutor' ? 'NIP / NIK Dosen' : 'Nomor Induk Mahasiswa (NIM)')"></label>
                            <input type="text" name="nomor_induk" value="{{ old('nomor_induk', $user->nomor_induk) }}" required class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono text-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Email Resmi <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                        </div>
                    </div>

                    <!-- SEKSI KHUSUS 1: MAHASANTRI / MUDABBIR -->
                    <template x-if="currentRole === 'mahasantri' || currentRole === 'mudabbir'">
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 space-y-3">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <span>🎓</span><span>Data Akademik & Status Santri</span>
                            </span>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Jenis Kelamin</label>
                                    <select name="gender" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                                        <option value="L" {{ ($santri?->gender?->value ?? $santri?->gender) === 'L' ? 'selected' : '' }}>Laki-laki (Ikhwan)</option>
                                        <option value="P" {{ ($santri?->gender?->value ?? $santri?->gender) === 'P' ? 'selected' : '' }}>Perempuan (Akhwat)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Nomor WhatsApp Aktif</label>
                                    <input type="text" name="no_hp" value="{{ old('no_hp', $santri?->no_hp) }}" placeholder="081234567890" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Program Studi</label>
                                    <input type="text" name="prodi" value="{{ old('prodi', $santri?->prodi) }}" placeholder="Program Studi" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Fakultas</label>
                                    <input type="text" name="fakultas" value="{{ old('fakultas', $santri?->fakultas) }}" placeholder="Fakultas" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Tahun Angkatan</label>
                                    <input type="text" name="angkatan" value="{{ old('angkatan', $santri?->angkatan) }}" placeholder="2026" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Status Keaktifan</label>
                                    <select name="status" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                                        <option value="aktif" {{ ($santri?->status?->value ?? $santri?->status) === 'aktif' ? 'selected' : '' }}>🟢 Aktif di Asrama</option>
                                        <option value="nonaktif" {{ ($santri?->status?->value ?? $santri?->status) === 'nonaktif' ? 'selected' : '' }}>🔴 Nonaktif / Keluar</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Alamat Asal Santri</label>
                                <input type="text" name="alamat" value="{{ old('alamat', $santri?->alamat) }}" placeholder="Kab/Kota asal santri" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                            </div>
                        </div>
                    </template>

                    <!-- SEKSI KHUSUS 2: TUTOR PENGAJAR -->
                    <template x-if="currentRole === 'tutor'">
                        <div class="p-4 rounded-xl bg-indigo-50/70 dark:bg-slate-800/40 border border-indigo-200/60 dark:border-slate-700/60 space-y-3">
                            <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                                <span>👨‍🏫</span><span>Informasi Penugasan Tutor</span>
                            </span>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Nomor WhatsApp Pengajar</label>
                                <input type="text" name="no_hp" value="{{ old('no_hp', $santri?->no_hp) }}" placeholder="081234567890" class="w-full text-xs rounded-xl px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Biodata akademik, bidang ajar (PPTQ/Kitab Kuning), dan rekening honorarium Tutor otomatis diperbarui saat Tutor login menggunakan kredensial SAHAJA.
                            </p>
                        </div>
                    </template>

                    <!-- SEKSI KHUSUS 3: ADMINISTRATOR -->
                    <template x-if="currentRole === 'admin'">
                        <div class="p-4 rounded-xl bg-purple-50/70 dark:bg-slate-800/40 border border-purple-200/60 dark:border-slate-700/60 text-xs text-purple-900 dark:text-purple-300 space-y-1">
                            <span class="font-bold block">🛡️ Otoritas Penuh Administrator</span>
                            <p class="text-slate-600 dark:text-slate-400 text-[11px]">Akun ini memiliki hak akses level tertinggi: kelola kamar, data santri, plot tutor, presensi, dan sinkronisasi sistem SAHAJA.</p>
                        </div>
                    </template>

                    <!-- Kata Sandi -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Ubah Kata Sandi <span class="text-slate-400 font-normal">(Kosongkan jika tidak ingin mengubah kata sandi)</span>
                        </label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter" class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                    <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
