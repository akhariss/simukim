<?php

namespace Database\Seeders;

use App\Enums\AbsensiStatus;
use App\Enums\Gender;
use App\Enums\KelasType;
use App\Enums\PelanggaranStatus;
use App\Enums\PenempatanStatus;
use App\Enums\PerizinanStatus;
use App\Enums\SantriStatus;
use App\Models\Absensi;
use App\Models\Gedung;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokAnggota;
use App\Models\KelompokPengampu;
use App\Models\Pelanggaran;
use App\Models\PelanggaranJenis;
use App\Models\PenempatanKamar;
use App\Models\Penilaian;
use App\Models\Periode;
use App\Models\Perizinan;
use App\Models\PerizinanJenis;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
            $roleTutor = Role::firstOrCreate(['name' => 'tutor']);
            $roleMudabbir = Role::firstOrCreate(['name' => 'mudabbir']);
            $roleMahasantri = Role::firstOrCreate(['name' => 'mahasantri']);

            $adminUser = User::role('admin')->first() ?? User::firstOrCreate(
                ['nomor_induk' => 'NIP-ADM01'],
                ['name' => "Admin Asrama Ma'had", 'email' => 'admin@uinssc.ac.id', 'password' => Hash::make('password'), 'auth_source' => 'local', 'email_verified_at' => now()]
            );

            $periode = Periode::getActive() ?? Periode::firstOrCreate(
                ['kode' => '20261'],
                ['nama' => '2026 Ganjil', 'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'is_active' => true]
            );

            // 1. Tutor 1 - 5 (NIP: nip1 - nip5)
            $tutorUsers = [];
            for ($i = 1; $i <= 5; $i++) {
                $tutor = User::firstOrCreate(
                    ['nomor_induk' => "nip{$i}"],
                    ['name' => "Ust. Tutor {$i}, M.Ag.", 'email' => "tutor{$i}@uinssc.ac.id", 'password' => Hash::make('password'), 'auth_source' => 'local', 'email_verified_at' => now()]
                );
                $tutor->syncRoles([$roleTutor]);
                $tutorUsers[] = $tutor;
            }

            // 2. Mudabbir 1 - 6
            $mudabbirSantris = [];
            for ($i = 1; $i <= 6; $i++) {
                $u = User::firstOrCreate(
                    ['nomor_induk' => "mdb{$i}"],
                    ['name' => "Mudabbir {$i}", 'email' => "mudabbir{$i}@uinssc.ac.id", 'password' => Hash::make('password'), 'auth_source' => 'local', 'email_verified_at' => now()]
                );
                $u->syncRoles([$roleMudabbir]);
                $gender = ($i <= 3) ? Gender::L : Gender::P;
                $mudabbirSantris[] = Santri::firstOrCreate(
                    ['nomor_induk' => "mdb{$i}"],
                    ['user_id' => $u->id, 'periode_id' => $periode->id, 'gender' => $gender, 'status' => SantriStatus::Aktif, 'prodi' => "Ilmu Al-Qur'an dan Tafsir", 'fakultas' => 'Ushuluddin', 'semester' => 5, 'angkatan' => 2024, 'status_pembayaran' => 'lunas', 'nominal_tagihan' => 0]
                );
            }

            // 3. Mahasantri 1 - 20
            $mahasantriList = [];
            $prodis = ['Informatika', 'Pendidikan Agama Islam', 'Hukum Keluarga Islam', 'Ekonomi Syariah'];
            for ($i = 1; $i <= 20; $i++) {
                $u = User::firstOrCreate(
                    ['nomor_induk' => "mhs{$i}"],
                    ['name' => "Mahasantri {$i}", 'email' => "mhs{$i}@uinssc.ac.id", 'password' => Hash::make('password'), 'auth_source' => 'local', 'email_verified_at' => now()]
                );
                $u->syncRoles([$roleMahasantri]);
                $gender = ($i <= 10) ? Gender::L : Gender::P;
                $mahasantriList[] = Santri::firstOrCreate(
                    ['nomor_induk' => "mhs{$i}"],
                    ['user_id' => $u->id, 'periode_id' => $periode->id, 'gender' => $gender, 'status' => SantriStatus::Aktif, 'prodi' => $prodis[$i % 4], 'fakultas' => 'Fakultas Terkait', 'semester' => 1, 'angkatan' => 2026, 'status_pembayaran' => 'lunas', 'nominal_tagihan' => 0]
                );
            }

            // 4. Penempatan Kamar Otomatis
            $allSantri = array_merge($mudabbirSantris, $mahasantriList);
            $rooms = Kamar::withCount(['penempatanAktif as terisi_count'])->get();
            foreach ($allSantri as $s) {
                if (!PenempatanKamar::where('santri_id', $s->id)->where('status', PenempatanStatus::Aktif)->exists()) {
                    $kamar = $rooms->first(fn($r) => $r->gender === $s->gender && ($r->terisi_count < $r->kapasitas_maks));
                    if ($kamar) {
                        PenempatanKamar::create([
                            'periode_id' => $periode->id, 'santri_id' => $s->id, 'kamar_id' => $kamar->id,
                            'tanggal_masuk' => '2026-08-15', 'status' => PenempatanStatus::Aktif,
                            'catatan' => 'Penempatan berkala resmi', 'assigned_by' => $adminUser->id,
                        ]);
                        $kamar->terisi_count++;
                    }
                }
            }

            // 5. Kelas & Kelompok Kajian
            $kelas = Kelas::firstOrCreate(
                ['nama' => "Tahsin Al-Qur'an & Tajwid", 'periode_id' => $periode->id],
                ['tipe' => KelasType::Kelompok, 'is_active' => true]
            );
            $kelompok = Kelompok::firstOrCreate(
                ['kode' => 'KLP-01', 'periode_id' => $periode->id],
                ['nama' => 'Kelompok Kajian Al-Fatihah', 'kapasitas' => 30, 'is_active' => true, 'keterangan' => 'Halaqah Tahsin Mahasantri']
            );
            if (!empty($tutorUsers)) {
                KelompokPengampu::firstOrCreate(['kelompok_id' => $kelompok->id, 'tutor_id' => $tutorUsers[0]->id], ['kelas_id' => $kelas->id]);
            }
            foreach ($mahasantriList as $s) {
                KelompokAnggota::firstOrCreate(['kelompok_id' => $kelompok->id, 'santri_id' => $s->id], ['tanggal_masuk' => '2026-08-20']);
            }

            // 6. Penilaian & Presensi Pembelajaran
            $nilaiData = [88.5, 92.0, 78.0, 85.0, 95.0, 70.0, 84.0, 90.0, 82.5, 76.0, 94.0, 88.0, 81.0, 87.5, 93.0, 79.0, 86.0, 91.5, 83.0, 89.0];
            foreach ($mahasantriList as $idx => $s) {
                $score = $nilaiData[$idx] ?? 85.0;
                $huruf = $score >= 85 ? 'A' : ($score >= 75 ? 'B' : 'C');
                Penilaian::updateOrCreate(
                    ['kelas_id' => $kelas->id, 'santri_id' => $s->id],
                    ['kelompok_id' => $kelompok->id, 'nilai_angka' => $score, 'nilai_huruf' => $huruf, 'catatan' => 'Capaian baik', 'input_by' => $tutorUsers[0]->id]
                );

                // 4 Sesi Presensi
                for ($sesi = 1; $sesi <= 4; $sesi++) {
                    $st = ($idx === 3 && $sesi === 2) ? AbsensiStatus::Izin : (($idx === 5 && $sesi === 3) ? AbsensiStatus::Sakit : AbsensiStatus::Hadir);
                    Absensi::firstOrCreate(
                        ['kelas_id' => $kelas->id, 'santri_id' => $s->id, 'tanggal' => "2026-10-0{$sesi}"],
                        ['kelompok_id' => $kelompok->id, 'status' => $st, 'input_by' => $tutorUsers[0]->id]
                    );
                }
            }

            // 7. Data Pelanggaran Disiplin
            $jenisPel = PelanggaranJenis::all();
            if ($jenisPel->count() > 0 && count($mahasantriList) >= 5) {
                $pSamples = [
                    ['santri' => $mahasantriList[1], 'j' => $jenisPel[0], 'tgl' => '2026-10-10', 's' => PelanggaranStatus::Selesai, 'h' => 'Piket kebersihan mushalla'],
                    ['santri' => $mahasantriList[4], 'j' => $jenisPel[1] ?? $jenisPel[0], 'tgl' => '2026-10-18', 's' => PelanggaranStatus::Terdaftar, 'h' => 'Setoran juz 30'],
                    ['santri' => $mahasantriList[8], 'j' => $jenisPel[0], 'tgl' => '2026-10-25', 's' => PelanggaranStatus::Selesai, 'h' => 'Teguran lisan pembina'],
                    ['santri' => $mahasantriList[14], 'j' => $jenisPel[2] ?? $jenisPel[0], 'tgl' => '2026-11-02', 's' => PelanggaranStatus::Terdaftar, 'h' => 'Pemanggilan orang tua / wali'],
                ];
                foreach ($pSamples as $p) {
                    Pelanggaran::firstOrCreate(
                        ['santri_id' => $p['santri']->id, 'tanggal' => $p['tgl'], 'jenis_pelanggaran_id' => $p['j']->id],
                        ['periode_id' => $periode->id, 'poin' => $p['j']->poin_default ?? 10, 'deskripsi' => 'Insiden kedisiplinan santri', 'hukuman' => $p['h'], 'status' => $p['s'], 'input_by' => $adminUser->id]
                    );
                }
            }

            // 8. Data Perizinan Santri
            $jenisIzin = PerizinanJenis::all();
            if ($jenisIzin->count() > 0 && count($mahasantriList) >= 6) {
                $iSamples = [
                    ['santri' => $mahasantriList[2], 'j' => $jenisIzin[1] ?? $jenisIzin[0], 'm' => '2026-10-05', 's' => '2026-10-08', 'st' => PerizinanStatus::Selesai, 'a' => 'Keperluan keluarga di luar kota'],
                    ['santri' => $mahasantriList[6], 'j' => $jenisIzin[0], 'm' => '2026-10-12', 's' => '2026-10-15', 'st' => PerizinanStatus::Selesai, 'a' => 'Rawat inap di klinik'],
                    ['santri' => $mahasantriList[10], 'j' => $jenisIzin[2] ?? $jenisIzin[0], 'm' => '2026-11-05', 's' => '2026-11-09', 'st' => PerizinanStatus::Berlaku, 'a' => 'Delegasi lomba akademik kampus'],
                    ['santri' => $mahasantriList[15], 'j' => $jenisIzin[1] ?? $jenisIzin[0], 'm' => '2026-11-06', 's' => '2026-11-10', 'st' => PerizinanStatus::Berlaku, 'a' => 'Izin menjenguk orang tua'],
                ];
                foreach ($iSamples as $iz) {
                    Perizinan::firstOrCreate(
                        ['santri_id' => $iz['santri']->id, 'tanggal_mulai' => $iz['m']],
                        ['periode_id' => $periode->id, 'jenis_perizinan_id' => $iz['j']->id, 'tanggal_selesai' => $iz['s'], 'alasan' => $iz['a'], 'status' => $iz['st'], 'input_by' => $adminUser->id]
                    );
                }
            }

            Cache::flush();
        });
    }
}
