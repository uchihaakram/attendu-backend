<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    public function view(User $user, Session $session): bool
    {
        return $session->instructors()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function start(User $user, Session $session): bool
    {
        return $this->view($user, $session);
    }
}
