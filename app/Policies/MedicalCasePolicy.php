<?php

namespace App\Policies;

use App\Models\MedicalCase;
use App\Models\User;

class MedicalCasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MedicalCase $case): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, MedicalCase $case): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, MedicalCase $case): bool
    {
        return $user->isAdmin();
    }
}
