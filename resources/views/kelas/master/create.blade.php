<x-app-layout>
    <x-slot name="title">Tambah Kajian Baru & Jadwal Sesi</x-slot>

    <div
        x-data="{
            nama: '',
            tipe: 'kelompok',
            deskripsi: '',
            siklus: 'minggu',
            jumlahPertemuan: 2,
            sesiList: [
                { nama_sesi: 'Pertemuan 1', hari: 'Senin', jam_mulai: '05:30', jam_selesai: '06:30', ruangan: 'Masjid Utama' },
                { nama_sesi: 'Pertemuan 2', hari: 'Kamis', jam_mulai: '05:30', jam_selesai: '06:30', ruangan: 'Masjid Utama' }
            ],
            updateJumlahPertemuan(n) {
                let count = parseInt(n) || 1;
                if (count < 1) count = 1;
                if (count > 14) count = 14;
                this.jumlahPertemuan = count;

                const hariArr = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                while (this.sesiList.length < count) {
                    let idx = this.sesiList.length + 1;
                    let defaultHari = hariArr[(idx - 1) % 7];
                    this.sesiList.push({
                        nama_sesi: 'Pertemuan ' + idx,
                        hari: defaultHari,
                        jam_mulai: '05:30',
                        jam_selesai: '06:30',
                        ruangan: 'Masjid Utama'
                    });
                }
                if (this.sesiList.length > count) {
                    this.sesiList = this.sesiList.slice(0, count);
                }
            },
            tambahSesi() {
                this.updateJumlahPertemuan(this.sesiList.length + 1);
            },
            hapusSesi(index) {
                if (this.sesiList.length <= 1) return;
                this.sesiList.splice(index, 1);
                this.jumlahPertemuan = this.sesiList.length;
            }
        }"
        class="max-w-5xl mx-auto space-y-5"
    >
        {{-- Page Header Clean & Elegant --}}
        <x-page-header
            :breadcrumbs="[
                ['label' => 'Master Kajian', 'url' => route('kelas.master.index')],
                ['label' => 'Tambah Kajian Baru']
            ]"
            title="Tambah Kajian Baru"
            description="Lengkapi identitas kajian kurikulum dan tetapkan jadwal sesi pertemuannya."
        >
            <x-slot:actions>
                <a
                    href="{{ route('kelas.master.index') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 shadow-2xs hover:bg-slate-50 dark:hover:bg-slate-700/60 transition active:scale-98"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Form Body --}}
        <form method="POST" action="{{ route('kelas.master.store') }}" class="space-y-5">
            @csrf

            {{-- 1. Identitas Kajian --}}
            @include('kelas.master.partials.form-kajian-info')

            {{-- 2. Jadwal Sesi Pertemuan Otomatis --}}
            @include('kelas.master.partials.form-kajian-sesi')

            {{-- Action Buttons --}}
            <div class="pt-4 pb-12 sm:pb-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <a
                    href="{{ route('kelas.master.index') }}"
                    class="w-full sm:w-auto px-5 py-3 rounded-xl text-xs font-semibold text-center text-slate-600 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 transition active:scale-98 cursor-pointer"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Kajian & Jadwal</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
