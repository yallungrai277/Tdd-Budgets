<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Budget;
use Illuminate\Auth\Access\HandlesAuthorization;

class BudgetPolicy
{
    use HandlesAuthorization;

    public function edit(User $user, Budget $budget)
    {
        return $budget->user_id === $user->id;
    }

    public function update(User $user, Budget $budget)
    {
        return $budget->user_id === $user->id;
    }

    public function destroy(User $user, Budget $budget)
    {
        return $budget->user_id === $user->id;
    }
}