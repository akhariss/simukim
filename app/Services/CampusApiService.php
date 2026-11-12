<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CampusApiService
{
    protected string $baseUrl, $bridgeUrl, $sahajaUrl, $sahajaKey;
    protected int $timeout, $sahajaTimeout, $healthCacheTtl;
    protected float $connectTimeout;
    protected bool $verifySsl;
    protected ?string $currentToken = null;

    public function __construct()
    {
        $cfg = (array) config('services.campus_api', []);
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? 'https://bridge.uinssc.ac.id'), '/');
        $this->bridgeUrl = rtrim((string) ($cfg['bridge_url'] ?? $this->baseUrl), '/');
        $this->sahajaUrl = rtrim((string) ($cfg['sahaja_url'] ?? $this->baseUrl), '/');
        $this->sahajaKey = (string) ($cfg['sahaja_key'] ?? ($cfg['key'] ?? ''));
        $this->timeout = (int) min(5, max(1, (int) ($cfg['bridge_timeout'] ?? ($cfg['timeout'] ?? 5))));
        $this->sahajaTimeout = (int) min(5, max(1, (int) ($cfg['sahaja_timeout'] ?? $this->timeout)));
        $this->connectTimeout = (float) min(1.0, max(0.1, (float) ($cfg['connect_timeout'] ?? 1.0)));
        $this->healthCacheTtl = max(300, (int) ($cfg['health_cache_ttl'] ?? 300));
        $this->verifySsl = (bool) ($cfg['verify_ssl'] ?? true);
    }

    public function client()
    {
        $timeout = (int) min(5, max(1, (int) (config('services.campus_api.bridge_timeout') ?: config('services.campus_api.timeout', $this->timeout))));
        $http = Http::timeout($timeout)->connectTimeout($this->connectTimeout)->acceptJson();
        return config('services.campus_api.bridge_verify_ssl', (bool) config('services.campus_api.verify_ssl', $this->verifySsl)) ? $http : $http->withoutVerifying();
    }

    public function sahajaClient(?string $apiKey = null)
    {
        $key = $apiKey ?: $this->sahajaKey;
        $timeout = (int) min(5, max(1, (int) config('services.campus_api.sahaja_timeout', $this->sahajaTimeout)));
        $http = Http::timeout($timeout)->connectTimeout($this->connectTimeout)->acceptJson();
        if (!config('services.campus_api.sahaja_verify_ssl', (bool) config('services.campus_api.verify_ssl', $this->verifySsl))) $http = $http->withoutVerifying();
        return !empty($key) ? $http->withHeaders(['X-API-KEY' => $key]) : $http;
    }

    public function probeSocket(string $url, float $timeout = 0.05): bool
    {
        if (app()->runningUnitTests()) return true;
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? null;
        if (!$host) return false;
        $port = $parsed['port'] ?? (($parsed['scheme'] ?? 'http') === 'https' ? 443 : 80);
        $fp = @fsockopen((($parsed['scheme'] ?? '') === 'https' ? 'ssl://' : '') . $host, $port, $errno, $errstr, $timeout);
        if ($fp) { fclose($fp); return true; }
        return false;
    }

    public function checkHealth(bool $forceRefresh = false): bool
    {
        if (empty($this->bridgeUrl)) return false;
        if (!$forceRefresh && Cache::has('campus_api_health_status')) return (bool) Cache::get('campus_api_health_status');
        if (!$this->probeSocket($this->bridgeUrl, 0.05)) { Cache::put('campus_api_health_status', false, 60); return false; }
        try {
            $isHealthy = $this->client()->get($this->bridgeUrl)->successful() || $this->client()->get("{$this->bridgeUrl}/health")->successful();
            Cache::put('campus_api_health_status', $isHealthy, $this->healthCacheTtl);
            return $isHealthy;
        } catch (Exception) { Cache::put('campus_api_health_status', false, $this->healthCacheTtl); return false; }
    }

    public function healthCheck(bool $forceRefresh = false): bool { return $this->checkHealth($forceRefresh); }
    public function getConnectTimeout(): float { return $this->connectTimeout; }
    public function getTimeout(): int { return $this->timeout; }
    public function getSahajaTimeout(): int { return $this->sahajaTimeout; }
    public function getHealthCacheTtl(): int { return $this->healthCacheTtl; }
    public function getSahajaKey(): string { return $this->sahajaKey; }
    public function getToken(): ?string { return $this->currentToken ?: (function_exists('session') && request()?->hasSession() ? session('campus_token') : null); }
    public function getCampusToken(): ?string { return $this->getToken(); }
    public function forgetToken(): void { $this->setCampusToken(null); }
    public function setCampusToken(?string $token): void {
        $this->currentToken = $token;
        if (function_exists('session') && request()?->hasSession()) {
            if ($token) session(['campus_token' => $token, 'campus_token_created_at' => now()->timestamp]);
            else session()->forget(['campus_token', 'campus_token_created_at']);
        }
    }

    public function login(string $username, string $password): array
    {
        try {
            $res = $this->client()->asForm()->post("{$this->bridgeUrl}/api/v2/portal/login", ['username' => trim($username), 'password' => $password]);
            $json = $res->json() ?? [];
            if (!$res->successful()) {
                $msg = match($res->status()) {
                    401 => 'Kata sandi portal kampus tidak cocok.',
                    404 => 'Nomor Induk (NIM/NIP) tidak ditemukan di pangkalan data portal kampus.',
                    500, 502, 503, 504 => 'Server Portal SSO Kampus sedang offline.',
                    default => $json['message'] ?? 'NIM/NIP atau kata sandi tidak cocok.',
                };
                return ['success' => false, 'message' => $msg];
            }
            $token = $json['data']['token'] ?? $json['token'] ?? null;
            if ($token) $this->setCampusToken($token);
            return ['success' => true, 'token' => $token, 'data' => $json['data'] ?? $json, 'message' => $json['message'] ?? 'Login berhasil.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Gagal menghubungi server portal kampus: ' . $e->getMessage()];
        }
    }

    public function getProfil(?string $token = null): ?array
    {
        $tok = $token ?: $this->getToken();
        if (empty($tok)) return null;
        try {
            $res = $this->client()->withToken($tok)->get("{$this->bridgeUrl}/api/v2/profil");
            if ($res->status() === 401) { $this->forgetToken(); return null; }
            return $res->successful() ? ($res->json('data') ?? $res->json()) : null;
        } catch (Exception) { return null; }
    }

    public function logout(?string $token = null): bool
    {
        $tok = $token ?: $this->getToken();
        $this->forgetToken();
        $this->logoutTutorSahaja();
        if (!$tok || empty($this->bridgeUrl)) return true;
        try {
            $this->client()->timeout(2)->withToken($tok)->post("{$this->bridgeUrl}/api/v2/logout");
            return true;
        } catch (Exception) { return false; }
    }
    public function logoutBridge(?string $token = null): bool { return $this->logout($token); }

    public function verifyCredentials(string $identifier, string $password): array
    {
        $loginRes = $this->login($identifier, $password);
        if (empty($loginRes['success'])) {
            $tutorRes = $this->loginTutorSahaja($identifier, $password);
            if (!empty($tutorRes['success'])) {
                $u = $tutorRes['data']['user'] ?? [];
                $d = $u['dosen'] ?? [];
                $gender = in_array(strtolower($d['jenis_kelamin'] ?? ''), ['perempuan', 'p']) ? 'P' : 'L';
                return [
                    'success' => true, 'user_type' => 'pegawai', 'token' => $tutorRes['token'] ?? null,
                    'data' => [
                        'nomor_induk' => $d['nip'] ?? $d['nik'] ?? $u['username'] ?? $identifier,
                        'nama' => $d['nama'] ?? $u['name'] ?? $identifier, 'email' => $d['email'] ?? $u['email'] ?? "{$identifier}@uinssc.ac.id",
                        'gender' => $gender, 'prodi' => $d['prodi_s1'] ?? 'Umum', 'fakultas' => null, 'angkatan' => date('Y'), 'no_hp' => $d['no_wa'] ?? null,
                    ],
                ];
            }
            return $loginRes;
        }

        $token = $loginRes['token'] ?? null;
        $profil = ($token ? $this->getProfil($token) : null) ?: ($loginRes['data'] ?? []);
        $jenis = strtolower((string) ($profil['jenis_pengguna'] ?? $profil['role'] ?? ''));
        $isPegawai = !empty($profil['nip']) || !empty($profil['nidn']) || in_array($jenis, ['pegawai', 'dosen', 'tutor', 'pengajar', 'tendik'], true) || !empty($profil['is_tutor']);

        $rawName = trim((string) ($profil['nama'] ?? $profil['name'] ?? $identifier));
        $gelarD = trim((string) ($profil['gelar_depan'] ?? ''));
        $gelarB = trim((string) ($profil['gelar_belakang'] ?? ''));
        if (!empty($gelarD) && !str_starts_with($rawName, $gelarD)) $rawName = "{$gelarD} {$rawName}";
        if (!empty($gelarB) && !str_ends_with($rawName, $gelarB)) $rawName = "{$rawName}, {$gelarB}";

        $rawGender = strtoupper((string) ($profil['gender'] ?? $profil['jenis_kelamin'] ?? 'L'));
        $gender = in_array($rawGender, ['P', 'PEREMPUAN', 'WANITA', '2', 'FEMALE']) ? 'P' : 'L';
        $prodi = $profil['prodi'] ?? $profil['program_studi_nama'] ?? $profil['nama_prodi'] ?? 'Umum';
        if (in_array(strtolower(trim($prodi)), ['-', 'null', ''])) $prodi = 'Umum';
        $nomorInduk = $isPegawai ? ($profil['nip'] ?? $profil['nik'] ?? $profil['username'] ?? $identifier) : ($profil['nim'] ?? $profil['nip'] ?? $identifier);

        return [
            'success' => true, 'user_type' => $isPegawai ? 'pegawai' : 'mahasiswa', 'token' => $token,
            'data' => [
                'nomor_induk' => $nomorInduk, 'nama' => $rawName,
                'email' => $profil['email_kampus'] ?? $profil['email'] ?? "{$identifier}@uinssc.ac.id",
                'gender' => $gender, 'prodi' => $prodi, 'fakultas' => $profil['fakultas'] ?? null,
                'jabatan' => $profil['jabatan'] ?? null, 'angkatan' => (string) ($profil['angkatan'] ?? date('Y')),
                'no_hp' => $profil['no_hp'] ?? $profil['hp'] ?? $profil['no_wa'] ?? null,
                'foto' => $profil['foto'] ?? null, 'status_aktif' => $profil['status_aktif'] ?? true,
                'jenis_pengguna' => $jenis ?: ($isPegawai ? 'pegawai' : 'mahasiswa'),
            ],
        ];
    }

    public function fetchActivePeriode(): ?array { return ['kode' => '20261', 'academic_period_id' => 3, 'nama' => '2026 Ganjil', 'is_active' => true, 'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31']; }
    public function fetchPeriodes(): array { return [$this->fetchActivePeriode(), ['kode' => '20251', 'academic_period_id' => 1, 'nama' => 'Semester Ganjil 2025/2026', 'is_active' => false, 'tanggal_mulai' => '2025-08-01', 'tanggal_selesai' => '2026-01-31']]; }

    public function fetchMuqimStudents(?string $kodePeriode = null, array $filters = []): array
    {
        $kode = $kodePeriode ?? '20261';
        try {
            $params = array_merge(['kode_periode' => $kode, 'per_page' => 'all'], $filters);
            $res = $this->sahajaClient()->get("{$this->sahajaUrl}/api/mahasantri/mukim", $params);
            if (!$res->successful() && $res->status() === 404) $res = $this->sahajaClient()->get("{$this->sahajaUrl}/api/v1/mahasantri/mukim", $params);
            return $res->successful() ? ($res->json() ?? []) : [];
        } catch (Exception) { return []; }
    }

    public function fetchSahajaSummary(?string $kodePeriode = null): array
    {
        $res = $this->fetchMuqimStudents($kodePeriode, ['per_page' => 1]);
        $ok = !empty($res['data']) || (!empty($res['status']) && $res['status'] === 'success');
        return ['online' => $ok, 'kode_periode' => $kodePeriode ?? '20251', 'summary' => $res['summary'] ?? ['total_mukim' => 0, 'total_laki_laki' => 0, 'total_perempuan' => 0, 'sudah_dapat_kamar' => 0, 'belum_dapat_kamar' => 0]];
    }

    public function smartLookup(string $identifier): ?array
    {
        foreach ($this->fetchMuqimStudents('20251')['data'] ?? [] as $st) {
            if (($st['nim'] ?? '') === $identifier || str_contains(strtolower($st['nama'] ?? ''), strtolower($identifier))) {
                return ['success' => true, 'source_type' => 'mahasiswa', 'raw' => $st, 'data' => $st];
            }
        }
        return null;
    }

    public function loginTutorSahaja(string $username, string $password): array
    {
        try {
            $res = $this->client()->post("{$this->sahajaUrl}/api/v1/auth/login", ['username' => $username, 'password' => $password]);
            $json = $res->json() ?? [];
            if ($res->successful() && ($json['status'] ?? '') === 'success') {
                $user = $json['data']['user'] ?? [];
                if (($user['role'] ?? '') !== 'dosen' && empty($user['is_tutor'])) return ['success' => false, 'message' => 'Akun terdaftar di SAHAJA tetapi bukan berstatus Tutor/Dosen.'];
                $token = $json['data']['token'] ?? null;
                if ($token && function_exists('session') && request()?->hasSession()) session(['sahaja_tutor_token' => $token]);
                return ['success' => true, 'token' => $token, 'data' => $json['data']];
            }
            return ['success' => false, 'message' => $json['message'] ?? 'Kredensial SSO Tutor SAHAJA tidak valid.'];
        } catch (Exception $e) { return ['success' => false, 'message' => 'Gagal menghubungi server SSO SAHAJA: ' . $e->getMessage()]; }
    }

    public function logoutTutorSahaja(?string $token = null): bool
    {
        $tok = $token ?: (function_exists('session') ? session('sahaja_tutor_token') : null);
        if (!$tok) return true;
        try {
            $this->client()->withToken($tok)->post("{$this->sahajaUrl}/api/v1/auth/logout");
            if (function_exists('session')) session()->forget('sahaja_tutor_token');
            return true;
        } catch (Exception) { return false; }
    }
}
