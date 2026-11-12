<?php

namespace App\Policies;

use App\Models\Kelas;
use App\Models\User;

class AbsensiPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function manage(User $user, Kelas $kelas): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('tutor')) {
            return $kelas->tutor_id === $user->id;
        }

        return false;
    }
}
