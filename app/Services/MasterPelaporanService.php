<?php

namespace App\Services;

use App\Exports\MasterLaporanExport;
use App\Models\Kamar;
use App\Models\Pelanggaran;
use App\Models\Pengaturan;
use App\Models\Penilaian;
use App\Models\Periode;
use App\Models\Perizinan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class MasterPelaporanService
{
    /**
     * Generate Laporan Eksekutif Terpadu Ma'had (Consolidated Master Report)
     */
    public function exportMaster(int $periodeId, string $rentang, ?string $bulan, string $format, ?string $kategori = null): Response
    {
        $periode = Periode::find($periodeId) ?? Periode::getActive() ?? Periode::first();
        $isBulanan = ($rentang === 'bulanan' && !empty($bulan));
        $timestamp = date('Ymd_His');

        $dataOkupansi = $this->getOkupansiData();
        $dataNilai = $this->getNilaiData($periode->id);
        $dataKehadiran = $this->getKehadiranData($periode->id, $isBulanan ? $bulan : null);
        $dataPelanggaran = $this->getPelanggaranData($periode->id, $isBulanan ? $bulan : null);
        $dataPerizinan = $this->getPerizinanData($periode->id, $isBulanan ? $bulan : null);

        $executiveSummary = [
            'nama_periode'      => $periode->nama ?? 'Periode Akademik',
            'rentang_label'     => $isBulanan ? ('Bulan ' . date('F Y', strtotime($bulan . '-01'))) : 'Satu Periode Penuh',
            'total_kamar'       => count($dataOkupansi['rows']),
            'total_santri_huni' => $dataOkupansi['total_terisi'],
            'okupansi_persen'   => $dataOkupansi['okupansi_persen'],
            'total_santri_nilai'=> count($dataNilai['rows']),
            'rata_nilai_akhir'  => $dataNilai['rata_rata'],
            'rata_kehadiran'    => $dataKehadiran['rata_kehadiran'],
            'total_pelanggaran' => count($dataPelanggaran['rows']),
            'total_perizinan'   => count($dataPerizinan['rows']),
        ];

        if ($format === 'excel') {
            $sheetsData = [
                'Ringkasan Eksekutif' => [
                    'headers' => ['Indikator Kinerja Utama', 'Nilai / Keterangan Capaian'],
                    'rows' => [
                        ['Periode Akademik', $executiveSummary['nama_periode']],
                        ['Ruang Lingkup Rentang', $executiveSummary['rentang_label']],
                        ['Total Unit Kamar Asrama', $executiveSummary['total_kamar'] . ' Kamar'],
                        ['Total Mahasantri Menghuni', $executiveSummary['total_santri_huni'] . ' Mahasantri'],
                        ['Tingkat Okupansi Hunian', $executiveSummary['okupansi_persen'] . '%'],
                        ['Rata-rata Nilai Akademik Halaqah', (string) $executiveSummary['rata_nilai_akhir']],
                        ['Rata-rata Tingkat Kehadiran Belajar', $executiveSummary['rata_kehadiran'] . '%'],
                        ['Total Kasus Pelanggaran Disiplin', $executiveSummary['total_pelanggaran'] . ' Kasus'],
                        ['Total Permohonan Izin Santri', $executiveSummary['total_perizinan'] . ' Berkas'],
                    ],
                ],
                'Hunian Asrama' => ['headers' => $dataOkupansi['headers'], 'rows' => $dataOkupansi['rows']],
                'Akademik & Nilai' => ['headers' => $dataNilai['headers'], 'rows' => $dataNilai['rows']],
                'Presensi Pembelajaran' => ['headers' => $dataKehadiran['headers'], 'rows' => $dataKehadiran['rows']],
                'Kedisiplinan' => ['headers' => $dataPelanggaran['headers'], 'rows' => $dataPelanggaran['rows']],
                'Perizinan' => ['headers' => $dataPerizinan['headers'], 'rows' => $dataPerizinan['rows']],
            ];

            return Excel::download(new MasterLaporanExport($sheetsData), "Laporan_Eksekutif_Terpadu_{$timestamp}.xlsx");
        }

        $settings = [
            'nama_mahad'   => Pengaturan::get('nama_mahad', "Ma'had Al-Jami'ah"),
            'alamat'       => Pengaturan::get('alamat', "Kompleks Asrama Mahasantri UIN Siber Syekh Nurjati Cirebon"),
            'nama_kepala'  => Pengaturan::get('nama_kepala', "Dr. KH. Abdullah Shiddiq, M.Ag."),
            'nip_kepala'   => Pengaturan::get('nip_kepala', "197808152003121003"),
            'dicetak_oleh' => auth()->user()?->name ?? 'Administrator Sistem',
        ];

        $viewData = compact('periode', 'isBulanan', 'bulan', 'executiveSummary', 'dataOkupansi', 'dataNilai', 'dataKehadiran', 'dataPelanggaran', 'dataPerizinan', 'settings');

        if ($format === 'print') {
            return response()->view('pelaporan.master_print', $viewData);
        }

        return Pdf::loadView('pelaporan.master_print', array_merge($viewData, ['isPdf' => true]))
            ->setPaper('a4', 'portrait')
            ->download("Laporan_Eksekutif_Terpadu_{$timestamp}.pdf");
    }

    public function getOkupansiData(): array
    {
        $kamarList = Kamar::with(['lantai.gedung', 'penempatanAktif.santri.user'])->orderBy('nomor_kamar')->get();
        $headers = ['No', 'Gedung', 'Lantai', 'Kamar', 'Kapasitas', 'Terisi', 'Sisa Bed', 'Status', 'Daftar Penghuni'];
        $rows = [];
        $totKapasitas = 0; $totTerisi = 0;

        foreach ($kamarList as $i => $k) {
            $terisi = $k->penempatanAktif->count();
            $totKapasitas += $k->kapasitas_maks;
            $totTerisi += $terisi;
            $penghuni = $k->penempatanAktif->map(fn($p) => $p->santri?->user?->name ?? '-')->implode(', ');
            $rows[] = [
                $i + 1, $k->lantai?->gedung?->nama ?? '-', 'Lantai ' . ($k->lantai?->nomor_lantai ?? '-'),
                $k->nomor_kamar, $k->kapasitas_maks, $terisi, max(0, $k->kapasitas_maks - $terisi),
                ucfirst((string) ($k->status->value ?? $k->status)), $penghuni ?: 'Kosong',
            ];
        }

        return [
            'headers' => $headers, 'rows' => $rows, 'total_terisi' => $totTerisi,
            'total_kapasitas' => $totKapasitas, 'okupansi_persen' => $totKapasitas > 0 ? round(($totTerisi / $totKapasitas) * 100, 1) : 0,
        ];
    }

    public function getNilaiData(int $periodeId): array
    {
        $list = Penilaian::with(['santri.user', 'kelas'])->whereHas('kelas', fn($q) => $q->where('periode_id', $periodeId))->get();
        $headers = ['No', 'Mahasantri', 'NIM', 'Kelas Kajian', 'Nilai Angka', 'Huruf Mutu', 'Status'];
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
        return ['headers' => $headers, 'rows' => $rows, 'rata_rata' => $avg];
    }

    public function getKehadiranData(int $periodeId, ?string $bulan = null): array
    {
        $query = DB::table('mahad_absensi')
            ->join('mahad_kelas', 'mahad_absensi.kelas_id', '=', 'mahad_kelas.id')
            ->join('mahad_santri', 'mahad_absensi.santri_id', '=', 'mahad_santri.id')
            ->join('users', 'mahad_santri.user_id', '=', 'users.id')
            ->where('mahad_kelas.periode_id', $periodeId);

        if ($bulan) {
            $parts = explode('-', $bulan);
            $query->whereYear('mahad_absensi.tanggal', (int) $parts[0])->whereMonth('mahad_absensi.tanggal', (int) $parts[1]);
        }

        $records = $query->selectRaw('users.name as santri_nama, mahad_santri.nomor_induk as nim, mahad_kelas.nama as kelas_nama, COUNT(*) as total, SUM(mahad_absensi.status = "hadir") as hadir, SUM(mahad_absensi.status = "izin") as izin, SUM(mahad_absensi.status = "sakit") as sakit, SUM(mahad_absensi.status = "alfa") as alfa')
            ->groupBy('mahad_santri.id', 'mahad_kelas.id', 'users.name', 'mahad_santri.nomor_induk', 'mahad_kelas.nama')->get();

        $headers = ['No', 'Mahasantri', 'NIM', 'Kelas', 'Total Sesi', 'Hadir', 'Izin', 'Sakit', 'Alfa', '% Kehadiran'];
        $rows = []; $totalPct = 0;
        foreach ($records as $i => $r) {
            $pct = $r->total > 0 ? round(($r->hadir / $r->total) * 100, 1) : 0;
            $totalPct += $pct;
            $rows[] = [$i + 1, $r->santri_nama, $r->nim, $r->kelas_nama, $r->total, $r->hadir, $r->izin, $r->sakit, $r->alfa, $pct . '%'];
        }

        return ['headers' => $headers, 'rows' => $rows, 'rata_kehadiran' => count($rows) > 0 ? round($totalPct / count($rows), 1) : 0];
    }

    public function getPelanggaranData(int $periodeId, ?string $bulan = null): array
    {
        $query = Pelanggaran::with(['santri.user', 'jenisPelanggaran'])->where('periode_id', $periodeId);
        if ($bulan) {
            $parts = explode('-', $bulan);
            $query->whereYear('tanggal', (int) $parts[0])->whereMonth('tanggal', (int) $parts[1]);
        }
        $list = $query->orderByDesc('tanggal')->get();

        $headers = ['No', 'Mahasantri', 'NIM', 'Kategori', 'Tanggal', 'Sanksi Disiplin', 'Status'];
        $rows = [];
        foreach ($list as $i => $p) {
            $kat = $p->poin <= 5 ? 'Ringan' : ($p->poin >= 20 ? 'Berat' : 'Sedang');
            $statusVal = $p->status instanceof \BackedEnum ? $p->status->value : $p->status;
            $rows[] = [
                $i + 1, $p->santri?->user?->name ?? '-', $p->santri?->nomor_induk ?? '-',
                $kat, $p->tanggal?->format('d/m/Y') ?? '-', $p->hukuman ?: '-',
                $statusVal === 'selesai' ? 'Selesai Dilaksanakan' : 'Belum Dilaksanakan',
            ];
        }
        return ['headers' => $headers, 'rows' => $rows];
    }

    public function getPerizinanData(int $periodeId, ?string $bulan = null): array
    {
        $query = Perizinan::with(['santri.user', 'jenisPerizinan'])->where('periode_id', $periodeId);
        if ($bulan) {
            $parts = explode('-', $bulan);
            $query->whereYear('tanggal_mulai', (int) $parts[0])->whereMonth('tanggal_mulai', (int) $parts[1]);
        }
        $list = $query->orderByDesc('tanggal_mulai')->get();

        $headers = ['No', 'Mahasantri', 'NIM', 'Kategori Izin', 'Mulai', 'Selesai', 'Hari', 'Status'];
        $rows = [];
        foreach ($list as $i => $iz) {
            $durasi = ($iz->tanggal_mulai && $iz->tanggal_selesai) ? ($iz->tanggal_mulai->diffInDays($iz->tanggal_selesai) + 1) : 1;
            $statusVal = $iz->status instanceof \BackedEnum ? $iz->status->value : $iz->status;
            $rows[] = [
                $i + 1, $iz->santri?->user?->name ?? '-', $iz->santri?->nomor_induk ?? '-',
                $iz->jenisPerizinan?->nama ?? '-', $iz->tanggal_mulai?->format('d/m/Y') ?? '-',
                $iz->tanggal_selesai?->format('d/m/Y') ?? '-', $durasi,
                $statusVal === 'berlaku' ? 'Sedang Berlaku' : ($statusVal === 'selesai' ? 'Telah Kembali' : 'Dibatalkan'),
            ];
        }
        return ['headers' => $headers, 'rows' => $rows];
    }
}
