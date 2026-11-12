<?php

namespace App\Http\Controllers;

use App\Models\Perizinan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SecureFileController extends Controller
{
    /**
     * Layani berkas sensitif terotentikasi (Anti-Direct Public Access & Anti-Path Traversal)
     */
    public function show(Request $request, string $category, string $filename): BinaryFileResponse|Response
    {
        // 1. Sanitasi Ketat Nama Berkas (Anti Path Traversal)
        $cleanFilename = basename($filename);
        if ($cleanFilename !== $filename || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $cleanFilename)) {
            abort(400, 'Nama berkas tidak valid.');
        }

        $user = Auth::user();
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $subfolder = match ($category) {
            'santri-foto' => 'santri_foto',
            'bukti-perizinan' => 'bukti_perizinan',
            'laporan-kelas' => 'kelas_laporan',
            default => abort(404, 'Kategori berkas tidak ditemukan.'),
        };

        // 2. Pemeriksaan Hak Akses (Authorization Gatekeeper)
        $this->authorizeAccess($user, $category, $cleanFilename);

        // 3. Lokasi Penyimpanan Berkas (Periksa disk lokal privat terlebih dahulu, fallback ke public lama)
        $relativePath = $subfolder . '/' . $cleanFilename;
        $fullPath = Storage::disk('local')->path($relativePath);

        if (!file_exists($fullPath)) {
            $fallbackLocalPath = storage_path('app/' . $relativePath);
            if (file_exists($fallbackLocalPath)) {
                $fullPath = $fallbackLocalPath;
            } else {
                // Cek kompatibilitas folder di public storage jika belum dimigrasikan
                $legacyPublicPath = storage_path('app/public/' . ($category === 'santri-foto' ? 'santri/foto/' : $subfolder . '/') . $cleanFilename);
                if (file_exists($legacyPublicPath)) {
                    $fullPath = $legacyPublicPath;
                } elseif ($category === 'santri-foto') {
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="#f1f5f9"/><circle cx="50" cy="38" r="18" fill="#94a3b8"/><path d="M18 85 C22 62 78 62 82 85 Z" fill="#94a3b8"/></svg>';
                    return response($svg, 200, [
                        'Content-Type' => 'image/svg+xml',
                        'Cache-Control' => 'public, max-age=86400',
                    ]);
                } else {
                    abort(404, 'Berkas tidak ditemukan.');
                }
            }
        }

        // 4. Kirim Berkas dengan Header Keamanan Lengkap
        return response()->file($fullPath, [
            'Cache-Control' => 'private, no-transform, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    /**
     * Validasi Izin Akses Berdasarkan Peran dan Kepemilikan Objek
     */
    private function authorizeAccess($user, string $category, string $filename): void
    {
        $role = $user->roles->first()?->name ?? 'mahasantri';

        if ($category === 'bukti-perizinan') {
            // Admin dan Mudabbir memiliki hak akses penuh terhadap seluruh berkas bukti
            if (in_array($role, ['admin', 'mudabbir'])) {
                return;
            }

            // Mahasantri hanya diizinkan melihat berkas bukti milik dirinya sendiri (Anti-IDOR)
            if ($role === 'mahasantri') {
                $santriId = $user->santri?->id;
                $isOwner = Perizinan::where('santri_id', $santriId)
                    ->where(function ($q) use ($filename) {
                        $q->where('file_bukti', 'like', "%{$filename}");
                    })->exists();

                if (!$isOwner) {
                    abort(403, 'Anda tidak berhak mengakses dokumen bukti ini.');
                }
                return;
            }

            abort(403, 'Akses ditolak.');
        }

        if ($category === 'laporan-kelas') {
            if (!in_array($role, ['admin', 'tutor', 'mudabbir'])) {
                abort(403, 'Hanya pengajar dan pengurus yang dapat melihat lampiran laporan.');
            }
        }
    }
}
