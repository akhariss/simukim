<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PelanggaranJenis;
use App\Models\Perizinan;
use App\Models\PerizinanJenis;
use App\Models\Periode;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TutorKedisiplinanController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $activePeriode = Periode::getActive();
        $tab = $request->query('tab', 'perizinan'); // 'perizinan' | 'pelanggaran'
        $search = trim((string) $request->query('search'));

        // Dapatkan ID santri yang berada di kelompok yang diampu oleh tutor ini
        $myKelompokIds = DB::table('mahad_kelompok_pengampu')
            ->when(!$user->hasRole('admin'), fn ($q) => $q->where('tutor_id', $user->id))
            ->pluck('kelompok_id')
            ->unique()
            ->toArray();

        $mySantriIds = DB::table('mahad_kelompok_anggota')
            ->whereIn('kelompok_id', $myKelompokIds)
            ->pluck('santri_id')
            ->unique()
            ->toArray();

        // 1. Data Tab Perizinan
        $perizinanQuery = Perizinan::query()
            ->where('periode_id', $activePeriode?->id)
            ->whereIn('santri_id', $mySantriIds)
            ->with(['santri.user', 'santri.penempatanAktif.kamar.lantai.gedung', 'jenisPerizinan', 'inputBy']);

        if ($search !== '' && $tab === 'perizinan') {
            $perizinanQuery->where(fn($q) => $q->where('alasan', 'like', "%{$search}%")
                ->orWhereHas('santri', fn($sub) => $sub->where('nomor_induk', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))));
        }

        $perizinanList = $perizinanQuery->orderBy('tanggal_mulai', 'desc')->paginate(15, ['*'], 'izin_page')->withQueryString();

        $statsPerizinan = DB::table('mahad_perizinan')
            ->where('periode_id', $activePeriode?->id)
            ->whereIn('santri_id', $mySantriIds)
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status = "berlaku" THEN 1 ELSE 0 END), 0) as berlaku,
                COALESCE(SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END), 0) as selesai
            ')->first();

        // 2. Data Tab Pelanggaran
        $pelanggaranQuery = Pelanggaran::query()
            ->where('periode_id', $activePeriode?->id)
            ->whereIn('santri_id', $mySantriIds)
            ->with(['santri.user', 'santri.penempatanAktif.kamar.lantai.gedung', 'jenisPelanggaran', 'inputBy']);

        if ($search !== '' && $tab === 'pelanggaran') {
            $pelanggaranQuery->where(fn($q) => $q->where('deskripsi', 'like', "%{$search}%")->orWhere('hukuman', 'like', "%{$search}%")
                ->orWhereHas('santri', fn($sub) => $sub->where('nomor_induk', 'like', "%{$search}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))));
        }

        $pelanggaranList = $pelanggaranQuery->orderBy('tanggal', 'desc')->paginate(15, ['*'], 'pelanggaran_page')->withQueryString();

        $statsPelanggaran = DB::table('mahad_pelanggaran')
            ->where('periode_id', $activePeriode?->id)
            ->whereIn('santri_id', $mySantriIds)
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status = "terdaftar" THEN 1 ELSE 0 END), 0) as terdaftar,
                COALESCE(SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END), 0) as selesai
            ')->first();

        // 3. Dropdown Santri Binaan Khusus Kelompok Tutor
        $santriBinaanList = Santri::query()
            ->whereIn('id', $mySantriIds)
            ->where('status', 'aktif')
            ->select('id', 'user_id', 'nomor_induk', 'gender', 'prodi')
            ->with(['user:id,name', 'penempatanAktif.kamar.gedung'])
            ->orderBy('nomor_induk', 'asc')
            ->get()
            ->map(function ($s) {
                $kamar = $s->penempatanAktif?->kamar;
                $gedung = $kamar?->gedung;
                return [
                    'id' => $s->id,
                    'nim' => $s->nomor_induk,
                    'nama' => $s->user?->name ?? 'Santri',
                    'prodi' => $s->prodi ?: 'Umum',
                    'gender' => ($s->gender?->value ?? $s->gender) === 'L' ? 'Ikhwan' : 'Akhwat',
                    'kamar' => $kamar ? "{$gedung?->nama} (Kamar {$kamar->nomor_kamar})" : 'Belum Ber-Kamar',
                ];
            });

        $jenisIzinList = PerizinanJenis::where('is_active', true)->orderBy('nama', 'asc')->get();
        $jenisPelanggaranList = PelanggaranJenis::where('is_active', true)->orderBy('nama', 'asc')->get();

        return view('tutor.kedisiplinan.index', compact(
            'tab', 'activePeriode', 'search',
            'perizinanList', 'statsPerizinan', 'jenisIzinList',
            'pelanggaranList', 'statsPelanggaran', 'jenisPelanggaranList',
            'santriBinaanList'
        ));
    }
}

