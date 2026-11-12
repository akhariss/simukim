<?php

namespace App\Policies;

use App\Models\Perizinan;
use App\Models\User;

class PerizinanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Perizinan $perizinan): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'mudabbir', 'mahasantri']);
    }

    public function update(User $user, Perizinan $perizinan): bool
    {
        return $user->hasAnyRole(['admin', 'mudabbir']);
    }

    public function delete(User $user, Perizinan $perizinan): bool
    {
        return $user->hasRole('admin');
    }
}
