<x-app-layout title="Edit Laporan Bulanan — {{ $kelas->nama }}">
    <div class="space-y-4 sm:space-y-5"
        x-data="{
            files: [],
            dt: new DataTransfer(),
            catatanText: @js(old('catatan', $laporan->catatan)),
            replacePhotos: false,
            showPopupModal: @js($errors->any()),
            popupTitle: @js($errors->any() ? 'Periksa Kembali Isian Formulir' : ''),
            popupMessage: @js($errors->first() ?? ''),
            handleFileSelect(e) {
                const newFiles = Array.from(e.target.files);
                for (const f of newFiles) {
                    const ext = f.name.split('.').pop().toLowerCase();
                    if (!['jpg', 'jpeg', 'png', 'webp'].includes(ext) || !f.type.startsWith('image/')) {
                        this.popupTitle = 'Format Berkas Tidak Valid';
                        this.popupMessage = `Berkas '${f.name}' bukan format foto yang diizinkan.`;
                        this.showPopupModal = true;
                        continue;
                    }
                    if (f.size > 2 * 1024 * 1024) {
                        this.popupTitle = 'Ukuran Berkas Melebihi Batas';
                        this.popupMessage = `Berkas '${f.name}' melebihi batas 2 MB.`;
                        this.showPopupModal = true;
                        continue;
                    }
                    if (this.files.some(item => item.name === f.name && item.sizeBytes === f.size)) continue;
                    if (this.files.length >= 10) break;
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
                if (this.replacePhotos && this.files.length < 3) {
                    this.popupTitle = 'Foto Belum Cukup';
                    this.popupMessage = `Penggantian dokumentasi foto wajib melampirkan minimal 3 foto kegiatan.`;
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
                ['label' => 'Edit Laporan']
            ]"
            title="Edit Laporan Bulanan"
            description="Periode {{ $laporan->nama_bulan }} {{ $laporan->tahun }} — {{ $kelas->nama }} ({{ $kelompok->nama }})"
        >
            <x-slot:actions>
                <a href="{{ route('kelas.laporan.kelas', [$kelompok, $kelas]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <form method="POST" action="{{ route('kelas.laporan.update', $laporan) }}" enctype="multipart/form-data" @submit="validateSubmit" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="kelompok_id" value="{{ $kelompok->id }}">
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <input type="hidden" name="bulan" value="{{ $laporan->bulan }}">
                <input type="hidden" name="tahun" value="{{ $laporan->tahun }}">
                <input type="file" name="foto[]" multiple accept="image/jpeg,image/png,image/webp" class="hidden" x-ref="actualInput">

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Ringkasan Materi & Catatan Pelaksanaan *</label>
                    <textarea name="catatan" rows="4" required x-model="catatanText" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs leading-relaxed focus:ring-2 focus:ring-blue-500/20"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
                    <div>
                        <span class="block font-bold text-slate-800 dark:text-slate-200">Foto Dokumentasi Saat Ini ({{ $laporan->foto->count() }} Foto)</span>
                        <div class="flex flex-wrap gap-2.5 mt-2">
                            @foreach($laporan->foto as $f)
                                @php $secureUrl = route('secure.file', ['category' => 'laporan-kelas', 'filename' => basename($f->file_path)]); @endphp
                                <div class="size-20 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 relative">
                                    <img src="{{ $secureUrl }}" alt="{{ $f->file_name }}" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" x-model="replacePhotos" class="rounded text-blue-600">
                            <span>Ganti dokumentasi foto lama dengan foto baru (Min. 3 Foto)</span>
                        </label>
                    </div>

                    <div x-show="replacePhotos" x-cloak class="space-y-2 pt-2">
                        <label class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 cursor-pointer">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span x-text="files.length === 0 ? 'Pilih Foto Pengganti' : '+ Tambah Foto Lain'"></span>
                            <input type="file" multiple accept="image/jpeg,image/png,image/webp" @change="handleFileSelect" class="hidden">
                        </label>
                        <div x-show="files.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                            <template x-for="(item, index) in files" :key="index">
                                <div class="relative p-2 rounded-xl border border-slate-200 bg-slate-50 flex flex-col items-center">
                                    <img :src="item.preview" class="size-20 object-cover rounded-lg mb-1">
                                    <span class="text-[10px] truncate max-w-[80px]" x-text="item.name"></span>
                                    <button type="button" @click="removeFile(index)" class="absolute top-1 right-1 size-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs">&times;</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('kelas.laporan.kelas', [$kelompok, $kelas]) }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700">Simpan Perubahan Laporan</button>
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
                        Periksa
                    </button>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>

