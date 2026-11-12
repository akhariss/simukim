<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Penempatan — {{ $penempatan->santri->user->name ?? 'Mahasantri' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .print-card { box-shadow: none !important; border: 1px solid #000 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8 flex flex-col items-center justify-center font-sans">
    <!-- Toolbar Aksi -->
    <div class="no-print w-full max-w-xl mb-4 flex items-center justify-between">
        <a
            href="{{ route('fasilitas.penempatan.index') }}"
            class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5"
        >
            ← Kembali ke Daftar Penempatan
        </a>
        <button
            type="button"
            onclick="window.print()"
            class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow transition flex items-center gap-1.5 cursor-pointer"
        >
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak / Download PDF
        </button>
    </div>

    <!-- Kartu Penempatan -->
    <div class="print-card w-full max-w-xl bg-white border border-slate-300 rounded-2xl shadow-md p-6 sm:p-8 space-y-6">
        <!-- Kop Surat -->
        <div class="border-b-2 border-slate-800 pb-4 flex items-center gap-4">
            <div class="size-14 rounded-xl bg-slate-900 text-white font-black text-xl flex items-center justify-center shrink-0">
                M
            </div>
            <div class="flex-1">
                <div class="text-[11px] font-bold tracking-widest uppercase text-slate-500">Pondok Pesantren Mahasiswa</div>
                <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-tight">
                    MA'HAD AL-JAMI'AH
                </h1>
                <p class="text-[11px] text-slate-600 mt-0.5">
                    Sistem Pengelolaan Asrama • Kartu Tanda Penempatan Kamar
                </p>
            </div>
        </div>

        <!-- Judul Dokumen -->
        <div class="text-center">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 inline-block pb-1">
                KARTU PENEMPATAN MAHASANTRI
            </h2>
            <div class="text-[10px] text-slate-500 mt-1 font-mono">
                No. Registrasi: MQM-{{ date('Y') }}-{{ str_pad($penempatan->id, 5, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <!-- Biodata Mahasantri -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 grid grid-cols-2 gap-3 text-xs">
            <div>
                <span class="block text-[10px] font-medium text-slate-500">Nama Lengkap</span>
                <span class="font-bold text-slate-900">{{ $penempatan->santri->user->name ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-medium text-slate-500">NIM / Nomor Induk</span>
                <span class="font-mono font-bold text-slate-900">{{ $penempatan->santri->nomor_induk ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-medium text-slate-500">Program Studi</span>
                <span class="font-medium text-slate-800">{{ $penempatan->santri->prodi ?? '-' }} ({{ $penempatan->santri->angkatan ?? '-' }})</span>
            </div>
            <div>
                <span class="block text-[10px] font-medium text-slate-500">Gender / Kategori</span>
                <span class="font-medium text-slate-800">{{ $penempatan->santri->gender === 'L' ? 'Ikhwan (Laki-laki)' : 'Akhwat (Perempuan)' }}</span>
            </div>
        </div>

        <!-- Lokasi Kamar Asrama (Highlight) -->
        <div class="border-2 border-blue-600/30 bg-blue-50/50 rounded-2xl p-4 grid grid-cols-3 gap-3 text-center">
            <div>
                <span class="block text-[10px] font-bold uppercase text-slate-500">Gedung</span>
                <span class="text-sm font-black text-slate-900 mt-0.5 block">
                    {{ $penempatan->kamar->lantai->gedung->nama ?? '-' }}
                </span>
            </div>
            <div class="border-x border-blue-200">
                <span class="block text-[10px] font-bold uppercase text-slate-500">Lantai</span>
                <span class="text-sm font-black text-slate-900 mt-0.5 block">
                    Lt. {{ $penempatan->kamar->lantai->nomor_lantai ?? '-' }}
                </span>
            </div>
            <div>
                <span class="block text-[10px] font-bold uppercase text-blue-700">Nomor Kamar</span>
                <span class="text-base font-black text-blue-700 mt-0.5 block">
                    {{ $penempatan->kamar->nomor_kamar ?? '-' }}
                </span>
            </div>
        </div>

        <!-- Informasi Tambahan & Validitas -->
        <div class="grid grid-cols-2 gap-3 text-xs border-t border-slate-200 pt-3">
            <div>
                <span class="text-slate-500">Tanggal Masuk:</span>
                <strong class="text-slate-800 ml-1">{{ $penempatan->tanggal_masuk ? $penempatan->tanggal_masuk->format('d F Y') : '-' }}</strong>
            </div>
            <div class="text-right">
                <span class="text-slate-500">Status Penempatan:</span>
                <strong class="text-emerald-700 ml-1 uppercase">{{ $penempatan->status }}</strong>
            </div>
        </div>

        <!-- Tanda Tangan -->
        <div class="pt-8 grid grid-cols-2 gap-8 text-center text-xs">
            <div>
                <p class="text-slate-500 text-[11px]">Mahasantri Penghuni,</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-900 border-t border-slate-300 pt-1 inline-block min-w-[140px]">
                    {{ $penempatan->santri->user->name ?? '-' }}
                </p>
            </div>
            <div>
                <p class="text-slate-500 text-[11px]">Pengelola / Pembina Asrama,</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-900 border-t border-slate-300 pt-1 inline-block min-w-[140px]">
                    {{ $penempatan->assignedBy->name ?? 'Admin Asrama' }}
                </p>
            </div>
        </div>

        <!-- Catatan Kaki -->
        <div class="border-t border-dashed border-slate-200 pt-3 text-[10px] text-slate-400 text-center">
            *Kartu ini sah sebagai bukti resmi hak tinggal di asrama Ma'had Al-Jami'ah. Harap disimpan dengan baik.
        </div>
    </div>
</body>
</html>
