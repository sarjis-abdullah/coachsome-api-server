<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserPage extends Model
{
    protected $table= 'user_pages';

    public function getUserPage($user, $page)
    {
        return $this->where('page_id', $page->id)->where('user_id', $user->id)->first();
    }
}
