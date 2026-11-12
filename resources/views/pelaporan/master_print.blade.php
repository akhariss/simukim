<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja & Tata Kelola — {{ $settings['nama_mahad'] ?? 'Ma\'had Al-Jami\'ah' }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 12mm 15mm 12mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 8.5pt; line-height: 1.35; color: #111827; margin: 0; padding: 0; }
        .no-print { display: block; }
        @media print { .no-print { display: none !important; } }
        .banner-print { font-family: Arial, sans-serif; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 8px 12px; margin-bottom: 12px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; }
        .btn-print { background: #2563eb; color: #ffffff; border: none; padding: 5px 14px; border-radius: 5px; font-size: 8.5pt; font-weight: bold; cursor: pointer; }
        .kop-wrapper { text-align: center; border-bottom: 2.5px solid #000; padding-bottom: 6px; margin-bottom: 2px; }
        .kop-subline { border-bottom: 0.75px solid #000; margin-bottom: 12px; }
        .kop-kemenag { font-size: 9.5pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
        .kop-univ { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin: 1px 0; }
        .kop-mahad { font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 1px 0; }
        .kop-alamat { font-family: Arial, sans-serif; font-size: 7.5pt; color: #374151; margin-top: 2px; }
        .doc-title-block { text-align: center; margin-bottom: 12px; }
        .doc-title { font-size: 11.5pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .doc-subtitle { font-size: 9pt; margin-top: 3px; }
        .sec-heading { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; margin: 10px 0 4px 0; padding-bottom: 2px; border-bottom: 1px solid #374151; }
        .formal-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8pt; }
        .formal-table th { background-color: #f3f4f6; color: #111827; font-weight: bold; border: 1px solid #4b5563; padding: 4px 5px; text-align: left; }
        .formal-table td { border: 1px solid #9ca3af; padding: 3.5px 5px; vertical-align: top; }
        .formal-table tr:nth-child(even) { background-color: #fafafa; }
        .text-center { text-align: center; }
        .summary-box { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8pt; }
        .summary-box td { border: 1px solid #d1d5db; padding: 4px 8px; }
        .summary-box td.label { background-color: #f9fafb; font-weight: bold; width: 35%; }
        .sign-wrapper { width: 100%; margin-top: 18px; page-break-inside: avoid; }
        .sign-block { float: right; width: 220px; text-align: center; }
        .sign-date { font-size: 8.5pt; }
        .sign-role { font-size: 8.5pt; font-weight: bold; margin-top: 2px; }
        .sign-space { height: 48px; }
        .sign-name { font-size: 9pt; font-weight: bold; text-decoration: underline; }
        .sign-nip { font-size: 8pt; margin-top: 1px; }
    </style>
</head>
<body>
    @if(empty($isPdf))
    <div class="no-print banner-print">
        <span style="font-weight: 600; color: #1e293b;">Buku Laporan Kinerja &amp; Tata Kelola — Dokumen Resmi Pimpinan</span>
        <div><button onclick="window.print()" class="btn-print">Cetak Dokumen</button></div>
    </div>
    @endif

    <!-- KOP SURAT RESMI -->
    <div class="kop-wrapper">
        <div class="kop-kemenag">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
        <div class="kop-univ">UNIVERSITAS ISLAM NEGERI SIBER SYEKH NURJATI CIREBON</div>
        <div class="kop-mahad">{{ strtoupper($settings['nama_mahad'] ?? "MA'HAD AL-JAMI'AH") }}</div>
        <div class="kop-alamat">{{ $settings['alamat'] ?? 'Kompleks Asrama Mahasantri, Jl. Perjuangan By Pass Sunyaragi Kesambi Kota Cirebon' }}</div>
    </div>
    <div class="kop-subline"></div>

    <!-- JUDUL LAPORAN -->
    <div class="doc-title-block">
        <div class="doc-title">LAPORAN KINERJA &amp; TATA KELOLA LEMBAGA</div>
        <div class="doc-subtitle">
            Periode: <strong>{{ $executiveSummary['nama_periode'] }}</strong> | Cakupan: <strong>{{ $executiveSummary['rentang_label'] }}</strong>
        </div>
    </div>

    <!-- RINGKASAN EKSEKUTIF KINERJA -->
    <div class="sec-heading">I. Ringkasan Indikator Kinerja Utama</div>
    <table class="summary-box">
        <tr>
            <td class="label">Kapasitas &amp; Tingkat Okupansi Hunian</td>
            <td>{{ $executiveSummary['total_santri_huni'] }} Mahasantri Menghuni / {{ $executiveSummary['total_kamar'] }} Unit Kamar ({{ $executiveSummary['okupansi_persen'] }}%)</td>
        </tr>
        <tr>
            <td class="label">Rata-rata Nilai Akademik Halaqah</td>
            <td>{{ $executiveSummary['rata_nilai_akhir'] }} / 100 (Dari {{ $executiveSummary['total_santri_nilai'] }} Mahasantri Terdaftar)</td>
        </tr>
        <tr>
            <td class="label">Rata-rata Kehadiran Belajar Mahasantri</td>
            <td>{{ $executiveSummary['rata_kehadiran'] }}%</td>
        </tr>
        <tr>
            <td class="label">Catatan Pelanggaran Kedisiplinan</td>
            <td>{{ $executiveSummary['total_pelanggaran'] }} Kasus Tercatat</td>
        </tr>
        <tr>
            <td class="label">Total Permohonan Izin Santri</td>
            <td>{{ $executiveSummary['total_perizinan'] }} Berkas Disetujui</td>
        </tr>
    </table>

    <!-- BAGIAN 1: HUNIAN ASRAMA -->
    <div class="sec-heading">II. Rekapitulasi Okupansi Asrama</div>
    <table class="formal-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Gedung</th>
                <th>Lantai</th>
                <th class="text-center">Kamar</th>
                <th class="text-center">Kapasitas</th>
                <th class="text-center">Terisi</th>
                <th class="text-center">Sisa</th>
                <th>Status</th>
                <th>Penghuni Asrama</th>
            </tr>
        </thead>
        <tbody>
            @forelse(array_slice($dataOkupansi['rows'], 0, 12) as $row)
            <tr>
                <td class="text-center">{{ $row[0] }}</td>
                <td>{{ $row[1] }}</td>
                <td>{{ $row[2] }}</td>
                <td class="text-center">{{ $row[3] }}</td>
                <td class="text-center">{{ $row[4] }}</td>
                <td class="text-center">{{ $row[5] }}</td>
                <td class="text-center">{{ $row[6] }}</td>
                <td>{{ $row[7] }}</td>
                <td>{{ $row[8] }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center" style="font-style: italic; color: #6b7280;">Tidak ada data okupansi kamar.</td></tr>
            @endforelse
            @if(count($dataOkupansi['rows']) > 12)
            <tr><td colspan="9" class="text-center" style="font-style: italic; color: #4b5563; background: #f9fafb;">... Menampilkan 12 dari {{ count($dataOkupansi['rows']) }} unit kamar. Unduh format Excel untuk data lengkap ...</td></tr>
            @endif
        </tbody>
    </table>

    <!-- BAGIAN 2: AKADEMIK & NILAI -->
    <div class="sec-heading">III. Rekapitulasi Capaian Nilai Pembelajaran</div>
    <table class="formal-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Nama Mahasantri</th>
                <th>NIM</th>
                <th>Kelas Kajian</th>
                <th class="text-center">Nilai Angka</th>
                <th class="text-center">Mutu</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataNilai['rows'] as $r)
            <tr>
                <td class="text-center">{{ $r[0] }}</td>
                <td>{{ $r[1] }}</td>
                <td>{{ $r[2] }}</td>
                <td>{{ $r[3] }}</td>
                <td class="text-center">{{ $r[4] }}</td>
                <td class="text-center">{{ $r[5] }}</td>
                <td class="text-center">{{ $r[6] }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center" style="font-style: italic; color: #6b7280;">Tidak ada catatan nilai pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- BAGIAN 3: KEDISIPLINAN & PERIZINAN -->
    <div class="sec-heading">IV. Rekapitulasi Kedisiplinan &amp; Perizinan Santri</div>
    <table class="formal-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Nama Mahasantri</th>
                <th>NIM</th>
                <th>Klasifikasi Kejadian</th>
                <th>Tanggal</th>
                <th>Tindakan / Sanksi Disiplin</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPelanggaran['rows'] as $p)
            <tr>
                <td class="text-center">{{ $p[0] }}</td>
                <td>{{ $p[1] }}</td>
                <td>{{ $p[2] }}</td>
                <td>{{ $p[3] }}</td>
                <td>{{ $p[4] }}</td>
                <td>{{ $p[5] }}</td>
                <td>{{ $p[6] }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center" style="font-style: italic; color: #6b7280;">Nihil kasus pelanggaran disiplin pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- LEMBAR PENGESAHAN RESMI -->
    <div class="sign-wrapper">
        <div class="sign-block">
            <div class="sign-date">Cirebon, {{ date('d F Y') }}</div>
            <div class="sign-role">Mudir / Kepala Ma'had Al-Jami'ah,</div>
            <div class="sign-space"></div>
            <div class="sign-name">{{ $settings['nama_kepala'] ?? 'Dr. KH. Abdullah Shiddiq, M.Ag.' }}</div>
            <div class="sign-nip">NIP. {{ $settings['nip_kepala'] ?? '197808152003121003' }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
