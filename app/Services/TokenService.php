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

    public function getUniqueId($pre, $l = 10)
    {
        return $pre . '-' . substr(md5(uniqid(time(), true)), 0, $l);
    }
}
