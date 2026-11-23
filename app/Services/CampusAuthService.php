<?php

namespace App\Services;

use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Lantai;
use App\Models\PenempatanKamar;
use App\Models\Periode;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CampusAuthService
{
    protected CampusApiService $api;
    public function __construct(CampusApiService $api) { $this->api = $api; }

    /**
     * Otentikasi Login (Auto-Sinkronisasi on-the-fly via SAHAJA & Kampus SSO)
     */
    public function authenticate(string $identifier, string $password): User
    {
        $identifier = trim($identifier);
        $localUser = User::where(fn($q) => $q->where('nomor_induk', $identifier)->orWhere('email', $identifier))->first();

        try { if ($localUser && $localUser->password && Hash::check($password, $localUser->password)) return $localUser; } catch (\Throwable) {}
        $invalidMsg = 'Username atau kata sandi yang Anda masukkan tidak sesuai.';
        if ($localUser && $localUser->auth_source === 'local') {
            throw ValidationException::withMessages(['email' => $invalidMsg, 'username' => $invalidMsg, 'login_id' => $invalidMsg])->redirectTo(route('login'));
        }

        $verifyResult = $this->api->verifyCredentials($identifier, $password);
        if (empty($verifyResult['success'])) {
            throw ValidationException::withMessages(['email' => $invalidMsg, 'username' => $invalidMsg, 'login_id' => $invalidMsg])->redirectTo(route('login'));
        }

        if (!$localUser) {
            $lookup = $this->api->smartLookup($identifier);
            if (!empty($lookup['raw'])) {
                return $this->syncSahajaStudent($lookup['raw']);
            }

            // Jika bukan santri mukim di SAHAJA dan belum terdaftar di SIMUKIM lokal:
            // Hanya izinkan jika user_type adalah pegawai/dosen (untuk calon tutor)
            $isPegawai = ($verifyResult['user_type'] ?? '') === 'pegawai';
            if (!$isPegawai) {
                throw ValidationException::withMessages([
                    'login_id' => 'Akun portal akademik Anda aktif, namun Anda belum terdaftar sebagai Mahasantri Mukim di Ma\'had Al-Jami\'ah. Silakan hubungi pengelola asrama atau lakukan pendaftaran asrama melalui portal SAHAJA.',
                    'username' => 'Akun portal akademik Anda aktif, namun Anda belum terdaftar sebagai Mahasantri Mukim di Ma\'had Al-Jami\'ah. Silakan hubungi pengelola asrama atau lakukan pendaftaran asrama melalui portal SAHAJA.',
                    'email'    => 'Akun portal akademik Anda aktif, namun Anda belum terdaftar sebagai Mahasantri Mukim di Ma\'had Al-Jami\'ah. Silakan hubungi pengelola asrama atau lakukan pendaftaran asrama melalui portal SAHAJA.',
                ])->redirectTo(route('login'));
            }

            $user = $this->upsertCampusRoleUser($identifier, 'tutor');
        } else {
            $user = $localUser;
        }

        $data = $verifyResult['data'] ?? [];
        if (!empty($data['nama'])) $user->name = $data['nama'];
        if (!empty($data['email']) && !User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
            $user->email = $data['email'];
        }
        if ($user->auth_source === 'campus' && !is_null($user->password)) $user->password = null;
        $user->save();

        if (($user->hasRole('mahasantri') || $user->hasRole('mudabbir') || $user->santri) && !empty($data)) {
            $prodi = !empty($data['prodi']) && !in_array(strtolower($data['prodi']), ['umum', '-', 'null', '']) ? trim($data['prodi']) : ($user->santri?->prodi ?? 'Umum');
            $activePeriode = Periode::getActive();
            Santri::updateOrCreate(['user_id' => $user->id], array_filter([
                'periode_id'  => $user->santri?->periode_id ?? $activePeriode?->id,
                'nomor_induk' => $user->nomor_induk,
                'gender'      => $data['gender'] ?? 'L',
                'prodi'       => $prodi,
                'status'      => 'aktif',
                'angkatan'    => $data['angkatan'] ?? (strlen($user->nomor_induk) >= 2 ? ('20' . substr($user->nomor_induk, 0, 2)) : date('Y')),
                'no_hp'       => $data['no_hp'] ?? null,
                'fakultas'    => $data['fakultas'] ?? null,
                'alamat'      => $data['alamat'] ?? null,
                'hp_ayah'     => $data['hp_ayah'] ?? null,
                'email_ayah'  => $data['email_ayah'] ?? null,
                'hp_ibu'      => $data['hp_ibu'] ?? null,
                'email_ibu'   => $data['email_ibu'] ?? null,
            ], fn($v) => !is_null($v) && $v !== ''));
        }

        return $user;
    }

    public function syncSahajaTutor(array $data, ?User $existing = null): User
    {
        $u = $data['user'] ?? [];
        $d = $u['dosen'] ?? [];
        $nip = $d['nip'] ?? $d['nik'] ?? $u['username'] ?? '';
        $user = $existing ?? User::where('nomor_induk', $nip)->orWhere('email', $d['email'] ?? $u['email'] ?? '')->first();
        if (!$user) {
            $user = User::create([
                'nomor_induk' => $nip, 'name' => $d['nama'] ?? $u['name'] ?? 'Tutor Dosen',
                'email' => $d['email'] ?? $u['email'] ?? "{$nip}@uinssc.ac.id",
                'auth_source' => 'campus', 'email_verified_at' => now(),
            ]);
        } else {
            $user->update(['name' => $d['nama'] ?? $u['name'] ?? $user->name, 'email' => $d['email'] ?? $u['email'] ?? $user->email]);
        }
        $user->syncRoles(['tutor']);
        return $user;
    }

    public function assignSantri(string $nim, array $extra = []): User
    {
        $lookup = $this->api->smartLookup($nim);
        return !empty($lookup['raw']) ? $this->syncSahajaStudent($lookup['raw']) : $this->upsertCampusRoleUser($nim, 'mahasantri', $extra);
    }
    public function assignMudabbir(string $nim, array $extra = []): User
    {
        $lookup = $this->api->smartLookup($nim);
        if (!empty($lookup['raw'])) {
            $user = $this->syncSahajaStudent($lookup['raw']);
            $user->syncRoles(['mudabbir']);
            return $user;
        }
        return $this->upsertCampusRoleUser($nim, 'mudabbir', $extra);
    }
    public function assignTutor(string $id, array $extra = []): User
    {
        $lookup = $this->api->smartLookup($id);
        if (!empty($lookup['raw']) && ($lookup['source_type'] ?? '') === 'mahasiswa') {
            $user = $this->syncSahajaStudent($lookup['raw']);
            $user->syncRoles(['tutor']);
            return $user;
        }
        return $this->upsertCampusRoleUser($id, 'tutor', $extra);
    }

    private function upsertCampusRoleUser(string $identifier, string $role, array $extra = []): User
    {
        $lookup = $this->api->smartLookup($identifier);
        $api = $lookup['data'] ?? [];
        $nama = !empty($extra['nama']) ? trim($extra['nama']) : (!empty($api['nama']) ? $api['nama'] : ucfirst($role) . " {$identifier}");
        $email = $api['email'] ?? (!empty($extra['email']) ? $extra['email'] : "{$identifier}@uinssc.ac.id");
        $noHp = $api['no_hp'] ?? (!empty($extra['no_hp']) ? trim($extra['no_hp']) : null);

        return DB::transaction(function () use ($identifier, $role, $extra, $api, $nama, $email, $noHp) {
            $user = User::updateOrCreate(['nomor_induk' => $identifier], [
                'name' => $nama, 'email' => $email, 'auth_source' => 'campus', 'password' => null, 'email_verified_at' => now(),
            ]);
            if ($role === 'mahasantri' || $role === 'mudabbir' || $user->santri) {
                Santri::updateOrCreate(['user_id' => $user->id], [
                    'nomor_induk' => $identifier, 'gender' => $api['gender'] ?? ($extra['gender'] ?? 'L'),
                    'prodi' => $api['prodi'] ?? (!empty($extra['prodi']) ? trim($extra['prodi']) : 'Umum'),
                    'angkatan' => $api['angkatan'] ?? (!empty($extra['angkatan']) ? trim($extra['angkatan']) : date('Y')),
                    'no_hp' => $noHp, 'status' => 'aktif',
                ]);
            }
            if ($role === 'mahasantri' ? (!$user->hasRole('mudabbir') && !$user->hasRole('admin')) : true) {
                $user->syncRoles([$role]);
            }
            return $user;
        });
    }

    /**
     * Batch Sync Mahasantri Mukim dari SAHAJA API
     */
    public function syncAllMuqimMahasiswa(?string $kodePeriode = null, array $filters = []): array
    {
        $targetKode = $kodePeriode ?: ($this->api->fetchActivePeriode()['kode'] ?? '20261');
        $activePeriode = Periode::where('kode', $targetKode)->orWhere('kode', str_replace('-', '', $targetKode))->first();
        if (!$activePeriode) {
            $apiActive = $this->api->fetchActivePeriode();
            $activePeriode = Periode::updateOrCreate(['kode' => $apiActive['kode']], [
                'academic_period_id' => $apiActive['academic_period_id'] ?? 3, 'nama' => $apiActive['nama'] ?? '2026 Ganjil',
                'tanggal_mulai' => $apiActive['tanggal_mulai'] ?? now()->startOfYear(), 'tanggal_selesai' => $apiActive['tanggal_selesai'] ?? now()->endOfYear(),
            ]);
        }
        $activePeriode->update(['is_active' => true]);
        Periode::where('id', '!=', $activePeriode->id)->update(['is_active' => false]);
        \Illuminate\Support\Facades\Cache::forget('active_periode');
        \Illuminate\Support\Facades\Cache::forget('penempatan_kpi_counts');
        \Illuminate\Support\Facades\Cache::forget('penempatan_kpi_counts_' . $activePeriode->id);

        $kode = $activePeriode->kode;
        $res = $this->api->fetchMuqimStudents($kode, $filters);
        $students = $res['data'] ?? [];
        $synced = 0; $newCount = 0; $updatedCount = 0;

        $nims = array_filter(array_column($students, 'nim'));
        $existingMap = User::whereIn('nomor_induk', $nims)->pluck('id', 'nomor_induk')->all();

        foreach ($students as $st) {
            $nim = $st['nim'] ?? '';
            if (isset($existingMap[$nim])) { $updatedCount++; } else { $newCount++; }
            $this->syncSahajaStudent($st, $activePeriode);
            $synced++;
        }

        return [
            'synced' => $synced, 'total' => count($students),
            'new' => $newCount, 'updated' => $updatedCount,
            'summary' => $res['summary'] ?? [],
            'periode' => $activePeriode,
        ];
    }

    /**
     * Sinkronisasi data satu santri SAHAJA beserta alokasi kamar
     */
    public function syncSahajaStudent(array $st, ?Periode $periode = null): User
    {
        return DB::transaction(function () use ($st, $periode) {
            $email = $st['kontak']['email'] ?? "{$st['nim']}@student.uinssc.ac.id";
            if (User::where('email', $email)->where('nomor_induk', '!=', $st['nim'])->exists()) {
                $email = "{$st['nim']}@student.uinssc.ac.id";
            }

            $user = User::updateOrCreate(['nomor_induk' => $st['nim']], [
                'name' => $st['nama'], 'email' => $email, 'auth_source' => 'campus', 'email_verified_at' => now(),
            ]);

            $k = $st['kontak'] ?? [];
            $ortu = $k['kontak_darurat_ortu'] ?? [];
            $tagihan = $st['billing_tagihan']['tagihan_terakhir'] ?? [];
            $targetPeriode = $periode;
            if (!$targetPeriode && !empty($st['riwayat_pendaftaran']['kode_periode_akademik'])) {
                $targetPeriode = Periode::firstOrCreate(['kode' => $st['riwayat_pendaftaran']['kode_periode_akademik']], [
                    'academic_period_id' => $st['riwayat_pendaftaran']['academic_period_id'] ?? null, 'nama' => $st['riwayat_pendaftaran']['nama_periode_akademik'] ?? 'Semester Terkait',
                    'tanggal_mulai' => now()->startOfYear(), 'tanggal_selesai' => now()->endOfYear(), 'is_active' => false,
                ]);
            }
            $targetPeriode = $targetPeriode ?? Periode::getActive();

            $santri = Santri::updateOrCreate(['user_id' => $user->id], [
                'periode_id' => $targetPeriode?->id,
                'nomor_induk' => $st['nim'], 'gender' => $st['jenis_kelamin'] ?? 'L',
                'prodi' => $st['prodi'] ?? 'Umum', 'fakultas' => $st['fakultas'] ?? null,
                'semester' => (int) ($st['semester'] ?? 1), 'angkatan' => (string) ($st['angkatan'] ?? date('Y')),
                'no_hp' => $k['no_hp'] ?? null, 'wa_santri' => $k['wa_santri'] ?? null, 'alamat' => $k['alamat'] ?? null,
                'hp_ayah' => $ortu['hp_ayah'] ?? null, 'email_ayah' => $ortu['email_ayah'] ?? null,
                'hp_ibu' => $ortu['hp_ibu'] ?? null, 'email_ibu' => $ortu['email_ibu'] ?? null,
                'briva_number' => $st['billing_tagihan']['briva_number'] ?? null,
                'bsb_customer_id' => $st['billing_tagihan']['bsb_customer_id'] ?? null,
                'status_pembayaran' => $tagihan['status'] ?? 'Belum Bayar',
                'nominal_tagihan' => (int) ($tagihan['nominal'] ?? 1500000), 'status' => 'aktif',
            ]);

            if (!$user->hasRole('mudabbir') && !$user->hasRole('admin')) {
                $user->syncRoles(['mahasantri']);
            }

            if (!empty($st['alokasi_kamar']['is_allocated']) && $targetPeriode) {
                $gender = ($st['jenis_kelamin'] ?? 'L') === 'P' ? 'P' : 'L';
                $gedung = Gedung::firstOrCreate(['nama' => $st['alokasi_kamar']['blok'] ?? 'Gedung Asrama'], ['kode' => ($gender === 'P' ? 'KD' : 'JD'), 'gender_khusus' => $gender, 'is_active' => true]);
                $lantai = Lantai::firstOrCreate(['gedung_id' => $gedung->id, 'nomor_lantai' => 1], ['kapasitas_kamar_standar' => 10]);
                $kamar = Kamar::firstOrCreate(['lantai_id' => $lantai->id, 'nomor_kamar' => $st['alokasi_kamar']['nomor_kamar'] ?? '101'], ['gender' => $gender, 'kapasitas_maks' => 4, 'status' => 'tersedia']);
                PenempatanKamar::updateOrCreate(['santri_id' => $santri->id, 'periode_id' => $targetPeriode->id], [
                    'kamar_id' => $kamar->id, 'tanggal_masuk' => $st['alokasi_kamar']['tgl_masuk'] ?? now()->toDateString(), 'status' => 'aktif', 'assigned_by' => auth()->id() ?? 1,
                ]);
            }

            return $user;
        });
    }
}
