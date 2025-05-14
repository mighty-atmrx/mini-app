<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\Expert;
use App\Models\User;

class ServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Service $course): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Service $service)
    {
        $expert = Expert::where('user_id', $user->id)->first();
        return $expert->id === $service->expert_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Service $service): bool
    {
        $expert = Expert::where('user_id', $user->id)->first();
        return $expert->id === $service->expert_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Service $course): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Service $course): bool
    {
        return false;
    }
}
