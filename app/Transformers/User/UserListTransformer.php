<?php


namespace App\Transformers\User;


use App\Entities\User;
use App\Transformers\Category\CategoriesTransformer;
use App\Transformers\Profile\ProfileTransformer;
use League\Fractal;

class UserListTransformer extends Fractal\TransformerAbstract
{

    protected $availableIncludes = [
        'sportCategories',
        'profile'
    ];


    public function transform(User $item)
    {
        // User Data
        $id = $item->id;
        $firstName = $item->first_name;
        $lastName = $item->last_name;
        $email = $item->email;

        return [
            'id' => $id,
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'userName' => $item->user_name,
        ];

    }

    public function includeSportCategories(User $item)
    {
        return $this->collection($item->sportCategories, new CategoriesTransformer());
    }

    public function includeProfile(User $item)
    {
        return $this->item($item->profile, new ProfileTransformer());
    }
}
