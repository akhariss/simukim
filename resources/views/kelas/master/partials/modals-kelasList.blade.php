{{-- Modals Tambah Sesi Per Kajian --}}
@foreach($kelasList as $kelas)

    {{-- Modal Tambah Sesi untuk Kajian Ini --}}
    <x-modal name="modal-tambah-jadwal-{{ $kelas->id }}" maxWidth="md">
        <div class="p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Tambah Sesi - {{ $kelas->nama }}</h3>
                <button type="button" @click="$dispatch('close-modal', 'modal-tambah-jadwal-{{ $kelas->id }}')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('kelas.master.jadwal.store', $kelas) }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Sesi *</label>
                    <input type="text" name="nama_sesi" required placeholder="Contoh: Sesi Pagi / Pertemuan 3" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Hari Pertemuan *</label>
                    <select name="hari" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Jam Mulai *</label>
                        <input type="time" name="jam_mulai" required value="05:30" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Jam Selesai *</label>
                        <input type="time" name="jam_selesai" required value="06:30" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Lokasi / Ruangan</label>
                    <input type="text" name="ruangan" placeholder="Contoh: Masjid Utama / Aula Lantai 1" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="$dispatch('close-modal', 'modal-tambah-jadwal-{{ $kelas->id }}')" class="px-3 py-1.5 text-xs rounded-xl text-slate-500 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition">Simpan Sesi</button>
                </div>
            </form>
        </div>
    </x-modal>
@endforeach
