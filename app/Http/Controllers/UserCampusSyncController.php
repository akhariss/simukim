<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\AssignCampusMudabbirRequest;
use App\Http\Requests\User\AssignCampusSantriRequest;
use App\Http\Requests\User\AssignCampusTutorRequest;
use App\Models\User;
use App\Services\CampusApiService;
use App\Services\CampusAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserCampusSyncController extends Controller
{
    protected CampusApiService $campusApi;
    protected CampusAuthService $campusAuth;

    public function __construct(CampusApiService $campusApi, CampusAuthService $campusAuth)
    {
        $this->campusApi = $campusApi;
        $this->campusAuth = $campusAuth;
    }

    /**
     * Live Preview Smart Lookup dari Standalone Campus API (NIM atau NIP)
     */
    public function lookupCampus(Request $request): JsonResponse
    {
        $identifier = trim((string) ($request->input('id') ?? $request->input('nim') ?? $request->input('nip')));

        if (empty($identifier)) {
            return response()->json([
                'found' => false,
                'message' => 'NIM atau NIP tidak boleh kosong.',
            ], 400);
        }

        $lookup = $this->campusApi->smartLookup($identifier);

        if (!$lookup || empty($lookup['success'])) {
            return response()->json([
                'found' => false,
                'message' => "Nomor Induk '{$identifier}' tidak terdaftar di sistem kampus.",
            ], 404);
        }

        $data = $lookup['data'];
        $sourceType = $lookup['source_type'] ?? 'mahasiswa';

        $localUser = User::where('nomor_induk', $data['nomor_induk'])->with('roles', 'santri')->first();
        $localRole = $localUser?->roles->first()?->name;

        return response()->json([
            'found' => true,
            'source_type' => $sourceType,
            'data' => array_merge($data, [
                'local_registered' => (bool) $localUser,
                'local_role' => $localRole,
            ]),
        ]);
    }

    /**
     * Daftarkan Mahasantri Kampus Baru via NIM
     */
    public function assignCampusSantri(AssignCampusSantriRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $user = $this->campusAuth->assignSantri($validated['nim'], $validated);
            return redirect()->route('users.index', ['role' => 'mahasantri'])
                ->with('success', "Mahasantri {$user->name} (NIM: {$user->nomor_induk}) berhasil didaftarkan ke sistem Ma'had!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Angkat Mahasiswa Kampus Menjadi Mudabbir Ma'had
     */
    public function assignCampusMudabbir(AssignCampusMudabbirRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $user = $this->campusAuth->assignMudabbir($validated['nim'], $validated);
            return redirect()->route('users.index', ['role' => 'mudabbir'])
                ->with('success', "Mahasiswa {$user->name} (NIM: {$user->nomor_induk}) resmi diangkat sebagai MUDABBIR Ma'had!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Angkat Dosen/Mahasiswa Kampus Menjadi Tutor Ma'had
     */
    public function assignCampusTutor(AssignCampusTutorRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $user = $this->campusAuth->assignTutor($validated['identifier'], $validated);
            return redirect()->route('users.index', ['role' => 'tutor'])
                ->with('success', "Pengajar {$user->name} ({$user->nomor_induk}) resmi diangkat sebagai TUTOR Ma'had!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Batch Sync Seluruh Mahasantri Muqim dari Campus API
     */
    public function syncAllMuqimMahasiswa(): RedirectResponse
    {
        try {
            $result = $this->campusAuth->syncAllMuqimMahasiswa();
            return redirect()->route('users.index', ['role' => 'mahasantri'])
                ->with('success', "Sinkronisasi Berhasil: {$result['synced']} mahasantri muqim berhasil diperbarui dari pangkalan data kampus.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyinkronkan data mahasiswa kampus: ' . $e->getMessage());
        }
    }
}
