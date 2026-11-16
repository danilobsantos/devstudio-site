<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Admin, UserRole::Editor);
    }

    public function view(User $user, Page $page): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Page $page): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Page $page): bool
    {
        return $this->viewAny($user);
    }
}
