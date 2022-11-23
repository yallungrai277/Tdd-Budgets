<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function edit(User $user, Transaction $transaction)
    {
        return $transaction->user_id === $user->id;
    }

    public function update(User $user, Transaction $transaction)
    {
        return $transaction->user_id === $user->id;
    }

    public function destroy(User $user, Transaction $transaction)
    {
        return $transaction->user_id === $user->id;
    }
}