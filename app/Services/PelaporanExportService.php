<?php

namespace App\Services;

use App\Exports\GenericArrayExport;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Pengaturan;
use App\Models\Penilaian;
use App\Models\Perizinan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PelaporanExportService
{
    protected function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, $headers);
            foreach ($rows as $row) { fputcsv($handle, $row); }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Helper render view print, download PDF resmi, stream CSV, atau export Excel asli (.xlsx)
     */
    protected function renderOrStream(string $filename, string $judul, array $headers, array $rows, string $format, array $summary = [])
    {
        $timestamp = date('Ymd_His');

        if ($format === 'excel') {
            return Excel::download(new GenericArrayExport($headers, $rows), "{$filename}_{$timestamp}.xlsx");
        }
        if ($format === 'csv') {
            return $this->streamCsv("{$filename}_{$timestamp}.csv", $headers, $rows);
        }

        $settings = [
            'nama_mahad'   => Pengaturan::get('nama_mahad', "Ma'had Al-Jami'ah Terpadu"),
            'alamat'       => Pengaturan::get('alamat', "Kompleks Asrama Mahasantri UIN Siber Syekh Nurjati Cirebon"),
            'nama_kepala'  => Pengaturan::get('nama_kepala', "Dr. KH. Abdullah Shiddiq, M.Ag."),
            'nip_kepala'   => Pengaturan::get('nip_kepala', "197808152003121003"),
            'dicetak_oleh' => request()?->user()?->name ?? 'Administrator Sistem',
        ];

        $viewData = compact('judul', 'headers', 'rows', 'summary', 'settings');

        if ($format === 'print') {
            return response()->view('pelaporan.print', $viewData);
        }

        return Pdf::loadView('pelaporan.print', array_merge($viewData, ['isPdf' => true]))
            ->setPaper('a4', 'portrait')
            ->download("{$filename}_{$timestamp}.pdf");
    }

    /**
     * 1. Export Rekap Kehadiran
     */
    public function exportKehadiran(int $periodeId, ?int $kelasId, string $format, ?string $bulan = null)
    {
        $query = DB::table('mahad_absensi')
            ->join('mahad_kelas', 'mahad_absensi.kelas_id', '=', 'mahad_kelas.id')
            ->join('mahad_santri', 'mahad_absensi.santri_id', '=', 'mahad_santri.id')
            ->join('users', 'mahad_santri.user_id', '=', 'users.id')
            ->where('mahad_kelas.periode_id', $periodeId);

        if ($kelasId) $query->where('mahad_absensi.kelas_id', $kelasId);
        if ($bulan) {
            $parts = explode('-', $bulan);
            $query->whereYear('mahad_absensi.tanggal', (int) $parts[0])->whereMonth('mahad_absensi.tanggal', (int) $parts[1]);
        }

        $records = $query->selectRaw('users.name as santri_nama, mahad_santri.nomor_induk as nim, mahad_kelas.nama as kelas_nama, COUNT(*) as total, SUM(mahad_absensi.status = "hadir") as hadir, SUM(mahad_absensi.status = "izin") as izin, SUM(mahad_absensi.status = "sakit") as sakit, SUM(mahad_absensi.status = "alfa") as alfa, SUM(mahad_absensi.status = "terlambat") as terlambat')
            ->groupBy('mahad_santri.id', 'mahad_kelas.id', 'users.name', 'mahad_santri.nomor_induk', 'mahad_kelas.nama')->get();

        $headers = ['No', 'Nama Mahasantri', 'NIM', 'Kelas', 'Total Presensi', 'Hadir', 'Izin', 'Sakit', 'Alfa', 'Terlambat', '% Kehadiran'];
        $rows = [];
        foreach ($records as $i => $r) {
            $pct = $r->total > 0 ? round(($r->hadir / $r->total) * 100, 1) : 0;
            $rows[] = [$i + 1, $r->santri_nama, $r->nim, $r->kelas_nama, $r->total, $r->hadir, $r->izin, $r->sakit, $r->alfa, $r->terlambat, $pct . '%'];
        }

        $summary = [
            ['label' => 'Total Mahasantri', 'value' => count($rows) . ' Orang'],
            ['label' => 'Cakupan Kelas', 'value' => $kelasId ? 'Kelas Terpilih' : 'Semua Kelas'],
            ['label' => 'Ruang Lingkup', 'value' => $bulan ? 'Bulan ' . date('F Y', strtotime($bulan . '-01')) : 'Satu Periode Penuh'],
        ];
        return $this->renderOrStream('rekap_kehadiran', 'Rekapitulasi Kehadiran Mahasantri' . ($bulan ? " (Bulan {$bulan})" : ' (Satu Periode Penuh)'), $headers, $rows, $format, $summary);
    }

    /**
     * 2. Export Okupansi Kamar Asrama
     */
    public function exportOkupansi(?int $gedungId, string $format)
    {
        $query = Kamar::with(['lantai.gedung', 'penempatanAktif.santri.user']);
        if ($gedungId) {
            $query->whereHas('lantai', fn($q) => $q->where('gedung_id', $gedungId));
        }
        $kamarList = $query->orderBy('nomor_kamar', 'asc')->get();

        $headers = ['No', 'Gedung', 'Lantai', 'Nomor Kamar', 'Tipe Ruang', 'Kapasitas', 'Terisi', 'Sisa', 'Status', 'Daftar Penghuni'];
        $rows = [];
        foreach ($kamarList as $i => $k) {
            $penghuni = $k->penempatanAktif->map(fn($p) => $p->santri?->user?->name ?? '-')->implode(', ');
            $tipeRuang = $k->tipe_ruang instanceof \BackedEnum ? $k->tipe_ruang->value : ($k->tipe_ruang ?? '-');
            $statusKamar = $k->status instanceof \BackedEnum ? $k->status->value : ($k->status ?? '-');
            $kapasitas = $k->kapasitas_maks ?? 4;
            $terisi = $k->penempatanAktif->count();
            $rows[] = [
                $i + 1, $k->lantai?->gedung?->nama ?? '-', 'Lantai ' . ($k->lantai?->nomor_lantai ?? '-'),
                $k->nomor_kamar, $tipeRuang, $kapasitas, $terisi,
                max(0, $kapasitas - $terisi), $statusKamar,
                $penghuni ?: 'Kosong',
            ];
        }

        $totKapasitas = $kamarList->sum('kapasitas_maks');
        $totTerisi = $kamarList->sum(fn($k) => $k->penempatanAktif->count());
        $summary = [
            ['label' => 'Total Unit Kamar', 'value' => count($rows) . ' Kamar'],
            ['label' => 'Okupansi Hunian', 'value' => "{$totTerisi} / {$totKapasitas} Bed (" . ($totKapasitas > 0 ? round(($totTerisi/$totKapasitas)*100, 1) : 0) . "%)"],
            ['label' => 'Filter Gedung', 'value' => $gedungId ? 'Gedung Terpilih' : 'Semua Gedung'],
        ];

        return $this->renderOrStream('okupansi_kamar', 'Laporan Okupansi Kamar Asrama', $headers, $rows, $format, $summary);
    }

    /**
     * 3. Export Pelanggaran & Disiplin
     */
    public function exportPelanggaran(int $periodeId, ?string $status, string $format, ?string $bulan = null)
    {
        $query = Pelanggaran::with(['santri.user', 'jenisPelanggaran', 'periode'])->where('periode_id', $periodeId);
        if ($status) $query->where('status', $status);
        if ($bulan) {
            $parts = explode('-', $bulan);
            $query->whereYear('tanggal', (int) $parts[0])->whereMonth('tanggal', (int) $parts[1]);
        }
        $list = $query->orderBy('tanggal', 'desc')->get();

        $headers = ['No', 'Nama Mahasantri', 'NIM', 'Tingkat Pelanggaran', 'Tanggal Kejadian', 'Tindakan Sanksi', 'Deskripsi Kejadian', 'Status Pelaksanaan'];
        $rows = [];
        foreach ($list as $i => $p) {
            $jenisNama = match(true) {
                $p->poin <= 5 => 'Pelanggaran Ringan',
                $p->poin >= 20 => 'Pelanggaran Berat',
                default => 'Pelanggaran Sedang',
            };
            $statusVal = $p->status instanceof \App\Enums\PelanggaranStatus ? $p->status->value : $p->status;
            $statusLabel = $statusVal === 'selesai' ? 'Selesai Dilaksanakan' : 'Belum Dilaksanakan';
            $rows[] = [
                $i + 1, $p->santri?->user?->name ?? '-', $p->santri?->nomor_induk ?? '-',
                $jenisNama, $p->tanggal?->format('d/m/Y') ?? '-',
                $p->hukuman ?: '-', $p->deskripsi ?: '-',
                $statusLabel,
            ];
        }

        $selesai = $list->where('status', 'selesai')->count();
        $belum = $list->where('status', 'terdaftar')->count();
        $summary = [
            ['label' => 'Total Kasus Tercatat', 'value' => $list->count() . ' Kasus'],
            ['label' => 'Status Pelaksanaan', 'value' => "Selesai: {$selesai} | Belum: {$belum}"],
            ['label' => 'Ruang Lingkup', 'value' => $bulan ? 'Bulan ' . date('F Y', strtotime($bulan . '-01')) : 'Satu Periode Penuh'],
        ];
        return $this->renderOrStream('laporan_pelanggaran', 'Laporan Kedisiplinan & Pelanggaran' . ($bulan ? " (Bulan {$bulan})" : ' (Satu Periode Penuh)'), $headers, $rows, $format, $summary);
    }

    /**
     * 4. Export Perizinan Santri
     */
    public function exportPerizinan(int $periodeId, ?string $status, string $format, ?string $bulan = null)
    {
        $query = Perizinan::with(['santri.user', 'jenisPerizinan', 'periode'])->where('periode_id', $periodeId);
        if ($status) $query->where('status', $status);
        if ($bulan) {
            $parts = explode('-', $bulan);
            $query->whereYear('tanggal_mulai', (int) $parts[0])->whereMonth('tanggal_mulai', (int) $parts[1]);
        }
        $list = $query->orderBy('tanggal_mulai', 'desc')->get();

        $headers = ['No', 'Nama Mahasantri', 'NIM', 'Kategori Izin', 'Tanggal Mulai', 'Tanggal Selesai', 'Durasi (Hari)', 'Alasan', 'Status'];
        $rows = [];
        foreach ($list as $i => $iz) {
            $durasi = ($iz->tanggal_mulai && $iz->tanggal_selesai) ? ($iz->tanggal_mulai->diffInDays($iz->tanggal_selesai) + 1) : 1;
            $statusVal = $iz->status instanceof \App\Enums\PerizinanStatus ? $iz->status->value : $iz->status;
            $statusLabel = $statusVal === 'berlaku' ? 'Sedang Berlaku' : ($statusVal === 'selesai' ? 'Telah Kembali' : 'Dibatalkan');
            $rows[] = [
                $i + 1, $iz->santri?->user?->name ?? '-', $iz->santri?->nomor_induk ?? '-',
                $iz->jenisPerizinan?->nama ?? '-', $iz->tanggal_mulai?->format('d/m/Y') ?? '-',
                $iz->tanggal_selesai?->format('d/m/Y') ?? '-', $durasi,
                $iz->alasan, $statusLabel,
            ];
        }

        $bCount = $list->filter(fn($i) => ($i->status instanceof \App\Enums\PerizinanStatus ? $i->status->value : $i->status) === 'berlaku')->count();
        $sCount = $list->filter(fn($i) => ($i->status instanceof \App\Enums\PerizinanStatus ? $i->status->value : $i->status) === 'selesai')->count();
        $summary = [
            ['label' => 'Total Permohonan Izin', 'value' => $list->count() . ' Berkas'],
            ['label' => 'Status Berkas', 'value' => "Berlaku: {$bCount} | Selesai: {$sCount}"],
            ['label' => 'Ruang Lingkup', 'value' => $bulan ? 'Bulan ' . date('F Y', strtotime($bulan . '-01')) : 'Satu Periode Penuh'],
        ];
        return $this->renderOrStream('laporan_perizinan', 'Laporan Rekapitulasi Perizinan' . ($bulan ? " (Bulan {$bulan})" : ' (Satu Periode Penuh)'), $headers, $rows, $format, $summary);
    }

    /**
     * 5. Export Transkrip / Nilai Kelas
     */
    public function exportNilai(int $periodeId, ?int $kelasId, string $format)
    {
        $query = Penilaian::with(['santri.user', 'kelas'])->whereHas('kelas', fn($q) => $q->where('periode_id', $periodeId));
        if ($kelasId) $query->where('kelas_id', $kelasId);
        $list = $query->get();

        $headers = ['No', 'Nama Mahasantri', 'NIM', 'Kelas', 'Nilai Angka', 'Huruf Mutu', 'Status Kelulusan'];
        $rows = [];
        foreach ($list as $i => $n) {
            $angka = (float) ($n->nilai_angka ?? ($n->nilai_akhir ?? 0));
            $huruf = $n->nilai_huruf ?? ($n->huruf ?? match(true) {
                $angka >= 85 => 'A',
                $angka >= 75 => 'B',
                $angka >= 60 => 'C',
                $angka >= 50 => 'D',
                default      => 'E',
            });
            $status = $n->status_kelulusan ?? ($angka >= 60 ? 'Lulus' : 'Tidak Lulus');
            $rows[] = [
                $i + 1, $n->santri?->user?->name ?? '-', $n->santri?->nomor_induk ?? '-',
                $n->kelas?->nama ?? '-', $angka, $huruf, $status,
            ];
        }

        $avg = count($rows) > 0 ? round($list->avg('nilai_angka') ?? ($list->avg('nilai_akhir') ?? 0), 1) : 0;
        $summary = [
            ['label' => 'Total Peserta', 'value' => count($rows) . ' Mahasantri'],
            ['label' => 'Rata-rata Nilai', 'value' => (string) $avg],
            ['label' => 'Filter Kelas', 'value' => $kelasId ? 'Kelas Terpilih' : 'Semua Kelas'],
        ];

        return $this->renderOrStream('rekap_nilai', 'Rekapitulasi Nilai & Transkrip Kelas', $headers, $rows, $format, $summary);
    }
}
