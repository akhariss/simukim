<?php

namespace App\Imports;

use App\Models\Santri;
use App\Models\User;
use App\Services\CampusApiService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation
{
    public array $imported = [];
    public array $skipped  = [];
    public string $role;
    protected ?CampusApiService $apiService = null;

    public function __construct(string $role = 'mahasantri')
    {
        $this->role = $role;
        $this->apiService = app(CampusApiService::class);
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nim = trim((string) ($row['nim_nip'] ?? $row['nim'] ?? $row['nip'] ?? $row['nip_nim'] ?? $row['nip_id'] ?? $row['nomor_induk'] ?? ''));
            if (empty($nim) || str_starts_with($nim, '[Wajib]') || str_starts_with($nim, '[Opsional]')) {
                continue;
            }

            if (User::where('nomor_induk', $nim)->exists()) {
                $this->skipped[] = $nim;
                continue;
            }

            DB::transaction(function () use ($row, $nim) {
                $api = $this->apiService ? $this->apiService->smartLookup($nim) : null;

                $nama     = trim((string) ($row['nama'] ?? $row['nama_lengkap'] ?? $row['name'] ?? '')) ?: ($api['nama'] ?? "Pengguna {$nim}");
                $email    = trim((string) ($row['email'] ?? '')) ?: ($api['email'] ?? (in_array($this->role, ['mahasantri', 'mudabbir']) ? "{$nim}@student.uinssc.ac.id" : "{$nim}@uinssc.ac.id"));

                $rawGender = strtoupper(trim((string) ($row['jenis_kelamin'] ?? $row['gender'] ?? '')));
                $gender   = in_array($rawGender, ['L', 'P']) ? $rawGender : ($api['gender'] ?? 'L');

                $prodi    = trim((string) ($row['prodi'] ?? $row['unit_prodi'] ?? $row['unit'] ?? '')) ?: ($api['prodi'] ?? 'Umum');
                $fakultas = trim((string) ($row['fakultas'] ?? '')) ?: ($api['fakultas'] ?? null);

                $semester = (int) ($row['semester'] ?? $api['semester'] ?? 1);
                $angkatan = trim((string) ($row['angkatan'] ?? '')) ?: ($api['angkatan'] ?? date('Y'));

                $noHp     = trim((string) ($row['no_hp'] ?? '')) ?: ($api['no_hp'] ?? null);
                $wa       = trim((string) ($row['wa_santri'] ?? $row['no_hp'] ?? '')) ?: ($api['wa_santri'] ?? $noHp);
                $alamat   = trim((string) ($row['alamat'] ?? '')) ?: ($api['alamat'] ?? null);

                $namaWali = trim((string) ($row['nama_wali'] ?? '')) ?: null;
                $hpWali   = trim((string) ($row['no_hp_wali'] ?? '')) ?: null;

                $hpAyah   = trim((string) ($row['hp_ayah'] ?? '')) ?: ($api['kontak_darurat_ortu']['hp_ayah'] ?? null);
                $emailAyah= trim((string) ($row['email_ayah'] ?? '')) ?: ($api['kontak_darurat_ortu']['email_ayah'] ?? null);
                $hpIbu    = trim((string) ($row['hp_ibu'] ?? '')) ?: ($api['kontak_darurat_ortu']['hp_ibu'] ?? null);
                $emailIbu = trim((string) ($row['email_ibu'] ?? '')) ?: ($api['kontak_darurat_ortu']['email_ibu'] ?? null);

                $briva    = trim((string) ($row['briva_number'] ?? '')) ?: ($api['briva_number'] ?? null);
                $tagihan  = (int) ($row['nominal_tagihan'] ?? $api['nominal_tagihan'] ?? 1500000);
                $bayar    = trim((string) ($row['status_pembayaran'] ?? '')) ?: ($api['status_pembayaran'] ?? 'Belum Bayar');
                $status   = trim((string) ($row['status'] ?? '')) ?: ($api['status'] ?? 'aktif');

                $rawPassword = trim((string) ($row['password'] ?? ''));
                $password = $rawPassword ?: 'user';

                $user = User::create([
                    'name'              => $nama,
                    'email'             => $email,
                    'nomor_induk'       => $nim,
                    'auth_source'       => $rawPassword ? 'local' : 'campus',
                    'password'          => Hash::make($password),
                    'email_verified_at' => now(),
                ]);

                $user->syncRoles([$this->role]);

                if (in_array($this->role, ['mahasantri', 'mudabbir'])) {
                    Santri::create([
                        'user_id'           => $user->id,
                        'periode_id'        => \App\Models\Periode::getActive()?->id,
                        'nomor_induk'       => $nim,
                        'gender'            => $gender,
                        'prodi'             => $prodi,
                        'fakultas'          => $fakultas,
                        'semester'          => $semester,
                        'angkatan'          => $angkatan,
                        'no_hp'             => $noHp,
                        'wa_santri'         => $wa,
                        'alamat'            => $alamat,
                        'nama_wali'         => $namaWali,
                        'no_hp_wali'        => $hpWali,
                        'hp_ayah'           => $hpAyah,
                        'email_ayah'        => $emailAyah,
                        'hp_ibu'            => $hpIbu,
                        'email_ibu'         => $emailIbu,
                        'briva_number'      => $briva,
                        'nominal_tagihan'   => $tagihan,
                        'status_pembayaran' => $bayar,
                        'status'            => $status,
                    ]);
                }

                $this->imported[] = $nim;
            });
        }
    }

    public function rules(): array
    {
        return [
            '*.nim_nip' => 'nullable',
            '*.nim'     => 'nullable',
            '*.nip'     => 'nullable',
            '*.nip_nim' => 'nullable',
            '*.nip_id'  => 'nullable',
        ];
    }
}
