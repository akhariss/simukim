<x-app-layout title="Buat Laporan Bulanan — {{ $kelas->nama }}">
    <div class="space-y-4 sm:space-y-5"
        x-data="{
            files: [],
            dt: new DataTransfer(),
            catatanText: @js(old('catatan', $autoNarrative ?? '')),
            showPopupModal: @js($errors->any()),
            popupTitle: @js($errors->any() ? 'Periksa Kembali Isian Formulir' : ''),
            popupMessage: @js($errors->first() ?? ''),
            handleFileSelect(e) {
                const newFiles = Array.from(e.target.files);
                for (const f of newFiles) {
                    const ext = f.name.split('.').pop().toLowerCase();
                    const allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!allowedExts.includes(ext) || !f.type.startsWith('image/')) {
                        this.popupTitle = 'Format Berkas Tidak Valid';
                        this.popupMessage = `Berkas '${f.name}' bukan format foto yang diizinkan (JPG, PNG, WEBP).`;
                        this.showPopupModal = true;
                        continue;
                    }
                    if (f.size > 2 * 1024 * 1024) {
                        this.popupTitle = 'Ukuran Berkas Melebihi Batas';
                        this.popupMessage = `Berkas '${f.name}' melebihi batas 2 MB (${(f.size / 1024 / 1024).toFixed(1)} MB).`;
                        this.showPopupModal = true;
                        continue;
                    }
                    if (this.files.some(item => item.name === f.name && item.sizeBytes === f.size)) continue;
                    if (this.files.length >= 10) {
                        this.popupTitle = 'Batas Maksimal Berkas';
                        this.popupMessage = 'Maksimal 10 foto kegiatan yang dapat diunggah.';
                        this.showPopupModal = true;
                        break;
                    }
                    this.files.push({ file: f, name: f.name, sizeBytes: f.size, sizeFormatted: (f.size / 1024).toFixed(0) + ' KB', preview: URL.createObjectURL(f) });
                    this.dt.items.add(f);
                }
                $refs.actualInput.files = this.dt.files;
                e.target.value = '';
            },
            removeFile(index) {
                const removed = this.files.splice(index, 1)[0];
                if (removed && removed.preview) URL.revokeObjectURL(removed.preview);
                this.dt = new DataTransfer();
                for (const item of this.files) this.dt.items.add(item.file);
                $refs.actualInput.files = this.dt.files;
            },
            validateSubmit(e) {
                if (this.catatanText.trim().length < 7) {
                    this.popupTitle = 'Catatan Belum Lengkap';
                    this.popupMessage = 'Catatan kegiatan wajib diisi minimal 7 karakter.';
                    this.showPopupModal = true;
                    e.preventDefault();
                    return false;
                }
                if (this.files.length < 3) {
                    this.popupTitle = 'Foto Belum Cukup';
                    this.popupMessage = `Wajib melampirkan minimal 3 foto dokumentasi kegiatan (saat ini ${this.files.length} foto terpilih).`;
                    this.showPopupModal = true;
                    e.preventDefault();
                    return false;
                }
                return true;
            }
        }">

        <x-page-header
            :breadcrumbs="[
                ['label' => 'Kajian Saya', 'url' => route('tutor.kajian.index')],
                ['label' => 'Laporan Bulanan', 'url' => route('kelas.laporan.index')],
                ['label' => $kelas->nama, 'url' => route('kelas.laporan.kelas', [$kelompok, $kelas])],
                ['label' => 'Buat Laporan']
            ]"
            title="Kirim Laporan Bulanan"
            description="Laporan kegiatan bulanan kelas {{ $kelas->nama }} — Kelompok {{ $kelompok->nama }}"
        >
            <x-slot:actions>
                <a href="{{ route('kelas.laporan.kelas', [$kelompok, $kelas]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Riwayat</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Form Laporan Bulanan --}}
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <form method="POST" action="{{ route('kelas.laporan.store') }}" enctype="multipart/form-data" @submit="validateSubmit" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="kelompok_id" value="{{ $kelompok->id }}">
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <input type="file" name="foto[]" multiple accept="image/jpeg,image/png,image/webp" class="hidden" x-ref="actualInput">

                <div class="max-w-xs">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bulan Pelaporan *</label>
                    <select name="bulan" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs">
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $bi => $bn)
                            <option value="{{ $bi + 1 }}" {{ (int) old('bulan', date('m')) === ($bi + 1) ? 'selected' : '' }}>{{ $bn }}</option>
                        @endforeach
                    </select>
                </div>

                @if(isset($absensiStats))
                    <div class="p-3.5 rounded-xl border border-blue-200/60 dark:border-blue-900/40 bg-blue-50/50 dark:bg-blue-950/20 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-blue-900 dark:text-blue-200">Rekapitulasi Presensi Sesi Bulan Ini</span>
                            <span class="font-mono font-bold text-xs text-blue-700 dark:text-blue-300">{{ $attendancePct }}% Kehadiran</span>
                        </div>
                        <div class="grid grid-cols-4 gap-2 text-center text-[11px]">
                            <div class="p-1.5 rounded-lg bg-white/80 dark:bg-slate-900/80"><span class="text-slate-400 block text-[10px]">Pertemuan</span><span class="font-bold font-mono">{{ $absensiStats->total_sesi ?? 0 }} Sesi</span></div>
                            <div class="p-1.5 rounded-lg bg-white/80 dark:bg-slate-900/80"><span class="text-emerald-600 block text-[10px]">Hadir</span><span class="font-bold font-mono text-emerald-700">{{ $absensiStats->hadir ?? 0 }}</span></div>
                            <div class="p-1.5 rounded-lg bg-white/80 dark:bg-slate-900/80"><span class="text-amber-600 block text-[10px]">Izin/Sakit</span><span class="font-bold font-mono text-amber-700">{{ ($absensiStats->izin ?? 0) + ($absensiStats->sakit ?? 0) }}</span></div>
                            <div class="p-1.5 rounded-lg bg-white/80 dark:bg-slate-900/80"><span class="text-rose-600 block text-[10px]">Alfa</span><span class="font-bold font-mono text-rose-700">{{ $absensiStats->alfa ?? 0 }}</span></div>
                        </div>
                        <div class="pt-1 text-[11px] text-slate-600 dark:text-slate-400">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Draf Catatan Akademik:</span>
                            <p class="italic text-[10.5px] mt-0.5">{{ $autoNarrative }}</p>
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Ringkasan Materi & Catatan Pelaksanaan *</label>
                    <textarea name="catatan" rows="4" required x-model="catatanText" placeholder="Uraikan capaian pembelajaran materi, kendala santri, dan evaluasi bulanan..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs leading-relaxed focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Minimal 7 karakter uraian evaluasi pelaksanaan kajian bulanan.</p>
                </div>

                {{-- Safe Multi-File Upload Zone with Accumulator --}}
                <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <label class="block font-bold text-slate-800 dark:text-slate-200">Dokumentasi Foto Kegiatan (Min. 3 Foto, Maks. 10) *</label>
                            <p class="text-[11px] text-slate-400">Format: JPG, JPEG, PNG, WEBP. Maksimal 2 MB per berkas foto.</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold"
                            :class="files.length >= 3 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300'">
                            <span x-text="files.length + ' / 3 Foto Terpilih'"></span>
                            <span x-show="files.length < 3" class="ml-1 text-[10.5px]" x-text="'(kurang ' + (3 - files.length) + ' lagi)'"></span>
                        </span>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900/60 cursor-pointer transition">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span x-text="files.length === 0 ? 'Pilih Foto Dokumentasi (Bisa Sekaligus atau Bertahap)' : '+ Tambah Foto Lain'"></span>
                            <input type="file" multiple accept="image/jpeg,image/png,image/webp" @change="handleFileSelect" class="hidden">
                        </label>
                    </div>

                    <div x-show="files.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-2">
                        <template x-for="(item, index) in files" :key="index">
                            <div class="relative p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 flex flex-col items-center group">
                                <div class="size-24 rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-700 mb-2 w-full flex items-center justify-center">
                                    <img :src="item.preview" :alt="item.name" class="w-full h-full object-cover">
                                </div>
                                <p class="text-[11px] font-medium text-slate-700 dark:text-slate-300 truncate w-full text-center" :title="item.name" x-text="item.name"></p>
                                <span class="text-[10px] text-slate-400" x-text="item.sizeFormatted"></span>
                                <button type="button" @click="removeFile(index)" class="absolute top-1 right-1 size-5 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs hover:bg-rose-700 transition shadow-xs cursor-pointer">
                                    &times;
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('kelas.laporan.kelas', [$kelompok, $kelas]) }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-98 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Kirim Laporan Bulanan</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Modal Popup Notifikasi Kesalahan Validasi Laporan --}}
        <template x-teleport="body">
            <div x-show="showPopupModal" x-cloak data-modal-portal="true" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-3 sm:p-4">
                <div
                    x-show="showPopupModal"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-slate-900/60 transition-opacity"
                    @click="showPopupModal = false"
                ></div>

                <div
                    x-show="showPopupModal"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-2xl space-y-4 text-center z-10"
                    @click.stop
                >
                    <div class="size-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="popupTitle"></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="popupMessage"></p>
                    </div>
                    <button type="button" @click="showPopupModal = false" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white transition cursor-pointer">
                        Periksa &amp; Lengkapi
                    </button>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
