<?php

namespace App\Policies;

use App\Models\Kelas;
use App\Models\User;

class KelasPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Kelas $kelas): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Kelas $kelas): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('tutor') && $kelas->tutor_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Kelas $kelas): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can manage members of the model.
     */
    public function manageMembers(User $user, Kelas $kelas): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('tutor') && $kelas->tutor_id === $user->id);
    }
}
