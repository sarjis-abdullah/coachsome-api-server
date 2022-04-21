<?php

namespace App\Policies;

use App\Entities\User;
use App\InviteFriend;
use Illuminate\Auth\Access\HandlesAuthorization;

class InviteFriendPolicy
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
     * @param  \App\InviteFriend  $inviteFriend
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, InviteFriend $inviteFriend)
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
     * @param  \App\InviteFriend  $inviteFriend
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, InviteFriend $inviteFriend)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\InviteFriend  $inviteFriend
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, InviteFriend $inviteFriend)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\InviteFriend  $inviteFriend
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, InviteFriend $inviteFriend)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\InviteFriend  $inviteFriend
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, InviteFriend $inviteFriend)
    {
        //
    }
}
