<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        if ($user) {
            $user->loadMissing([
                'santri.penempatanAktif.kamar.lantai.gedung',
                'roles',
            ]);
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->auth_source !== 'campus') {
            $user->fill($request->only('name', 'email'));
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();
        }

        if ($request->has('no_hp') && $user->santri) {
            $user->santri->update([
                'no_hp' => trim((string)$request->no_hp) ?: null,
                'wa_santri' => trim((string)$request->no_hp) ?: null,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
