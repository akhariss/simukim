<?php

namespace App\Policies;

use App\Models\PenempatanKamar;
use App\Models\User;

class PenempatanKamarPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PenempatanKamar $penempatan): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'mudabbir']);
    }

    public function update(User $user, PenempatanKamar $penempatan): bool
    {
        return $user->hasAnyRole(['admin', 'mudabbir']);
    }

    public function delete(User $user, PenempatanKamar $penempatan): bool
    {
        return $user->hasRole('admin');
    }
}
