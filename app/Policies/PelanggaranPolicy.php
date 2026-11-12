<?php

namespace App\Policies;

use App\Models\Pelanggaran;
use App\Models\User;

class PelanggaranPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pelanggaran $pelanggaran): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'mudabbir', 'tutor']);
    }

    public function update(User $user, Pelanggaran $pelanggaran): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Pelanggaran $pelanggaran): bool
    {
        return $user->hasRole('admin');
    }
}
