<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Admin);
    }

    public function view(User $user, SiteSetting $setting): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user) && ! SiteSetting::query()->exists();
    }

    public function update(User $user, SiteSetting $setting): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, SiteSetting $setting): bool
    {
        return false;
    }
}
