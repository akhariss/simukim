<?php

namespace App\Http\Controllers\Kelompok;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kelompok\AssignTutorRequest;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokPengampu;
use App\Models\User;
use App\Services\KelompokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KelompokKajianController extends Controller
{
    protected KelompokService $service;

    public function __construct(KelompokService $service)
    {
        $this->service = $service;
    }

    public function show(Kelompok $kelompok, Kelas $kelas): View
    {
        if ($kelompok->periode_id !== $kelas->periode_id) {
            abort(404, 'Mata kajian tidak sesuai dengan periode kelompok ini.');
        }

        $kelompok->load([
            'periode',
            'anggota.santri.user',
            'anggota.santri.penempatanAktif.kamar',
        ]);

        $kelas->load(['jadwal', 'periode']);

        $pengampu = KelompokPengampu::where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->with('tutor')
            ->first();

        $tutors = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['tutor', 'admin']))
            ->orderBy('name')
            ->get(['id', 'name', 'nomor_induk', 'email']);

        $existingAssignments = KelompokPengampu::where('kelas_id', $kelas->id)
            ->whereHas('kelompok', fn ($q) => $q->where('periode_id', $kelompok->periode_id))
            ->with('kelompok')
            ->get()
            ->keyBy('tutor_id');

        $tipeVal = $kelas->tipe instanceof \App\Enums\KelasType ? $kelas->tipe->value : (string) $kelas->tipe;
        $isRestricted = in_array($tipeVal, ['kelompok', 'quran', 'kitab']);

        $tutorCandidates = $tutors->map(function ($t) use ($existingAssignments, $kelompok, $pengampu, $isRestricted) {
            $assignment = $existingAssignments->get($t->id);
            $isConflict = $isRestricted && $assignment && $assignment->kelompok_id !== $kelompok->id;
            $isCurrent = $pengampu && $pengampu->tutor_id === $t->id;

            return [
                'id' => $t->id,
                'name' => $t->name,
                'nomor_induk' => $t->nomor_induk ?? '-',
                'email' => $t->email,
                'is_current' => $isCurrent,
                'is_eligible' => !$isConflict,
                'conflict_kelompok' => $isConflict ? ($assignment->kelompok?->nama ?? 'Kelompok Lain') : null,
            ];
        });

        $unavailableCount = $tutorCandidates->where('is_eligible', false)->count();

        return view('kelompok.kajian.show', compact(
            'kelompok', 'kelas', 'pengampu', 'tutors', 'tutorCandidates', 'unavailableCount', 'isRestricted'
        ));
    }

    public function assignTutor(AssignTutorRequest $request, Kelompok $kelompok, Kelas $kelas): RedirectResponse
    {
        $tutorId = (int) $request->validated('tutor_id');

        try {
            $this->service->assignTutor($kelompok, $kelas->id, $tutorId);
            return back()->with('success', "Tutor pengampu untuk kajian {$kelas->nama} berhasil disimpan.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function unassignTutor(Kelompok $kelompok, Kelas $kelas): RedirectResponse
    {
        KelompokPengampu::where('kelompok_id', $kelompok->id)
            ->where('kelas_id', $kelas->id)
            ->delete();

        return back()->with('success', "Penugasan tutor untuk kajian {$kelas->nama} berhasil dilepas.");
    }
}

