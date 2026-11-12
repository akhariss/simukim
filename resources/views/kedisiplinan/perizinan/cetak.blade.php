<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Izin Mahasantri - {{ $perizinan->santri->nomor_induk }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 20mm; font-size: 12pt; }
            .card { border: none !important; box-shadow: none !important; }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111;
            background: #f8fafc;
            padding: 24px;
            line-height: 1.5;
        }
        .container {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 14px;
            margin-bottom: 24px;
        }
        .header h2 { margin: 0; font-size: 18pt; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .header h3 { margin: 4px 0; font-size: 14pt; font-weight: normal; }
        .header p { margin: 0; font-size: 10pt; color: #444; }
        .title { text-align: center; margin: 24px 0 20px 0; }
        .title h4 { margin: 0; font-size: 13pt; text-transform: uppercase; text-decoration: underline; }
        .title span { font-size: 10.5pt; color: #555; }
        table.data { width: 100%; margin: 16px 0; border-collapse: collapse; }
        table.data td { padding: 6px 4px; vertical-align: top; }
        table.data td.label { width: 32%; font-weight: bold; }
        table.data td.sep { width: 3%; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature { text-align: center; width: 45%; }
        .signature .space { height: 75px; }
        .btn-print {
            background: #2563eb; color: #fff; border: none; padding: 8px 16px;
            font-size: 13px; font-weight: 600; border-radius: 8px; cursor: pointer;
        }
        .btn-close {
            background: #64748b; color: #fff; border: none; padding: 8px 16px;
            font-size: 13px; font-weight: 600; border-radius: 8px; cursor: pointer;
        }
        .no-print-bar {
            max-width: 760px; margin: 0 auto 12px auto; display: flex; justify-content: space-between; align-items: center;
        }
    </style>
</head>
<body>
    <div class="no-print no-print-bar">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn-close">Tutup</button>
    </div>

    <div class="container">
        <!-- Kop Surat -->
        <div class="header">
            <h2>{{ $namaMahad }}</h2>
            <h3>SURAT KETERANGAN IZIN MAHASANTRI</h3>
            <p>{{ $alamatMahad }}</p>
        </div>

        <!-- Nomor Surat -->
        <div class="title">
            <h4>SURAT IZIN KELUAR ASRAMA</h4>
            <span>Nomor: {{ str_pad($perizinan->id, 4, '0', STR_PAD_LEFT) }}/IZN-MAHAD/{{ date('m/Y', strtotime($perizinan->tanggal_mulai)) }}</span>
        </div>

        <p>Yang bertanda tangan di bawah ini menerangkan bahwa mahasantri yang bersangkutan di bawah ini:</p>

        <table class="data">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="sep">:</td>
                <td><strong>{{ $perizinan->santri->user->name ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Nomor Induk Mahasiswa (NIM)</td>
                <td class="sep">:</td>
                <td>{{ $perizinan->santri->nomor_induk }}</td>
            </tr>
            <tr>
                <td class="label">Program Studi / Angkatan</td>
                <td class="sep">:</td>
                <td>{{ $perizinan->santri->prodi }} / {{ $perizinan->santri->angkatan }}</td>
            </tr>
            <tr>
                <td class="label">Kamar / Gedung Asrama</td>
                <td class="sep">:</td>
                <td>
                    @if($perizinan->santri->penempatanAktif && $perizinan->santri->penempatanAktif->kamar)
                        {{ $perizinan->santri->penempatanAktif->kamar->lantai->gedung->nama ?? '' }} • Kamar {{ $perizinan->santri->penempatanAktif->kamar->nomor_kamar }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Periode Akademik</td>
                <td class="sep">:</td>
                <td>{{ $perizinan->periode->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Keperluan / Kategori Izin</td>
                <td class="sep">:</td>
                <td><strong>{{ $perizinan->jenisPerizinan->nama ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Rentang Masa Izin</td>
                <td class="sep">:</td>
                <td>
                    {{ $perizinan->tanggal_mulai->translatedFormat('d F Y') }} s/d {{ $perizinan->tanggal_selesai->translatedFormat('d F Y') }}
                    ({{ $perizinan->tanggal_mulai->diffInDays($perizinan->tanggal_selesai) + 1 }} Hari)
                </td>
            </tr>
            <tr>
                <td class="label">Alasan Keperluan</td>
                <td class="sep">:</td>
                <td><em>"{{ $perizinan->alasan }}"</em></td>
            </tr>
        </table>

        <p>
            Diberikan izin untuk meninggalkan asrama Ma'had Al-Jami'ah sesuai rentang tanggal tersebut di atas dengan catatan wajib mematuhi norma akhlakul karimah dan kembali ke asrama tepat pada tanggal yang telah ditentukan.
        </p>

        <!-- Tanda Tangan -->
        <div class="footer">
            <div class="signature">
                <p>Mahasantri Pemohon,</p>
                <div class="space"></div>
                <p><strong>{{ $perizinan->santri->user->name ?? '-' }}</strong><br>NIM. {{ $perizinan->santri->nomor_induk }}</p>
            </div>

            <div class="signature">
                <p>Mengetahui,<br>Pengasuh / Kepala Ma'had</p>
                <div class="space"></div>
                <p><strong>{{ $kepalaMahad }}</strong><br>NIP. {{ $nipKepala }}</p>
            </div>
        </div>
    </div>
</body>
</html>
