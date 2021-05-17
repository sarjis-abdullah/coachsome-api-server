<?php


namespace App\Services;


class TokenService
{
    public function deleteUserAccessToken($user)
    {
        return $user->token()->delete();
    }

    public function createUserAccessToken($user)
    {
        return $user ? $user->createToken('MyApp')->accessToken : null;
    }

    public function getUserAccessToken($user)
    {
        return $user->token();
    }
}
