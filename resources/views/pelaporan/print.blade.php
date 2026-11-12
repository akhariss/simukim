<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul ?? 'Laporan Resmi Ma\'had' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 12mm 15mm 12mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
        }
        .banner-print {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 5px 14px;
            border-radius: 5px;
            font-size: 8.5pt;
            font-weight: bold;
            cursor: pointer;
        }
        .kop-wrapper {
            text-align: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 6px;
            margin-bottom: 2px;
        }
        .kop-subline {
            border-bottom: 0.75px solid #000;
            margin-bottom: 12px;
        }
        .kop-kemenag {
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-univ {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 1px 0;
        }
        .kop-mahad {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 1px 0;
        }
        .kop-alamat {
            font-family: Arial, sans-serif;
            font-size: 7.5pt;
            color: #374151;
            margin-top: 2px;
        }
        .doc-title-block {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .doc-subtitle {
            font-size: 8.5pt;
            margin-top: 3px;
            color: #374151;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        .summary-box td {
            border: 1px solid #d1d5db;
            padding: 3.5px 7px;
        }
        .summary-box td.label {
            background-color: #f9fafb;
            font-weight: bold;
            width: 30%;
        }
        .formal-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 8pt;
        }
        .formal-table th {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: bold;
            border: 1px solid #4b5563;
            padding: 4px 5px;
            text-align: left;
        }
        .formal-table td {
            border: 1px solid #9ca3af;
            padding: 3.5px 5px;
            vertical-align: top;
        }
        .formal-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .sign-wrapper {
            width: 100%;
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .sign-block {
            float: right;
            width: 220px;
            text-align: center;
        }
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
        <span style="font-weight: 600; color: #1e293b;">Dokumen Cetak Resmi — Format Standar Rekapitulasi Kampus</span>
        <div><button onclick="window.print()" class="btn-print">Cetak Dokumen</button></div>
    </div>
    @endif

    <!-- KOP SURAT RESMI -->
    <div class="kop-wrapper">
        <div class="kop-kemenag">Kementerian Agama Republik Indonesia</div>
        <div class="kop-univ">Universitas Islam Negeri Siber Syekh Nurjati Cirebon</div>
        <div class="kop-mahad">{{ $settings['nama_mahad'] ?? "Ma'had Al-Jami'ah" }}</div>
        <div class="kop-alamat">{{ $settings['alamat'] ?? 'Kompleks Asrama Mahasantri, Jl. Perjuangan By Pass Sunyaragi Kesambi Kota Cirebon' }}</div>
    </div>
    <div class="kop-subline"></div>

    <!-- JUDUL RESMI -->
    <div class="doc-title-block">
        <div class="doc-title">{{ strtoupper($judul ?? 'LAPORAN REKAPITULASI') }}</div>
        <div class="doc-subtitle">Pangkalan Data Tata Kelola Asrama &amp; Akademik Ma'had Al-Jami'ah</div>
    </div>

    <!-- RINGKASAN FORMAL (JIKA ADA) -->
    @if(!empty($summary))
    <table class="summary-box">
        @foreach($summary as $s)
        <tr>
            <td class="label">{{ $s['label'] }}</td>
            <td>{{ $s['value'] }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <!-- TABEL DATA FORMAL -->
    <table class="formal-table">
        <thead>
            <tr>
                @foreach($headers as $h)
                <th class="{{ $h === 'No' ? 'text-center' : '' }}">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                @foreach($row as $idx => $cell)
                <td class="{{ $idx === 0 ? 'text-center' : '' }}">{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($headers) }}" class="text-center" style="font-style: italic; color: #6b7280; padding: 12px;">
                    Tidak ada catatan data untuk kriteria ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PENGESAHAN RESMI -->
    <div class="sign-wrapper">
        <div class="sign-block">
            <div class="sign-date">Cirebon, {{ date('d F Y') }}</div>
            <div class="sign-role">Mudir Ma'had Al-Jami'ah,</div>
            <div class="sign-space"></div>
            <div class="sign-name">{{ $settings['nama_kepala'] ?? 'Dr. KH. Abdullah Shiddiq, M.Ag.' }}</div>
            <div class="sign-nip">NIP. {{ $settings['nip_kepala'] ?? '197808152003121003' }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
