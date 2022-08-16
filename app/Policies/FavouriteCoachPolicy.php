<?php

namespace App\Policies;

use App\Entities\User;
use App\FavouriteCoach;
use Illuminate\Auth\Access\HandlesAuthorization;

class FavouriteCoachPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Entities\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\FavouriteCoach  $favouriteCoach
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, FavouriteCoach $favouriteCoach)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Entities\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\FavouriteCoach  $favouriteCoach
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, FavouriteCoach $favouriteCoach)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\FavouriteCoach  $favouriteCoach
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, FavouriteCoach $favouriteCoach)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\FavouriteCoach  $favouriteCoach
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, FavouriteCoach $favouriteCoach)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\FavouriteCoach  $favouriteCoach
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, FavouriteCoach $favouriteCoach)
    {
        //
    }
}
