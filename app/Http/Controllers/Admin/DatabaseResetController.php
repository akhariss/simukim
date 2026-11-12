<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetDatabaseRequest;
use App\Services\Admin\DatabaseResetService;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Auth;

class DatabaseResetController extends Controller
{
    /**
     * Eksekusi reset database ke kondisi awal bersih
     */
    public function reset(ResetDatabaseRequest $request, DatabaseResetService $service): RedirectResponse
    {
        $stats = $service->resetToFreshStarter($request->user());

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'status',
                "Database berhasil di-reset total ke kondisi awal bersih! Seluruh fasilitas kamar ({$stats['kamar']}), kelas ({$stats['kelas']}), dan data riwayat telah dikosongkan. Seluruh sesi telah diakhiri. Silakan login kembali dengan akun Admin atau akun sampel."
            );
    }
}
