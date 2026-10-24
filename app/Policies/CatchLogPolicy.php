<?php

namespace App\Policies;

use App\Models\CatchLog;
use App\Models\User;

class CatchLogPolicy
{
    public function delete(User $user, CatchLog $catchLog): bool
    {
        return $catchLog->user_id === $user->id;
    }
}
