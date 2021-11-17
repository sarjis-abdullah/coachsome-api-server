<?php

namespace App\Http\Resources\Contact;

use App\Entities\User;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactUserCollection extends ResourceCollection
{
    private $user;
    public function __construct($resource, User $user)
    {
        $this->user = $user;
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function($item){
            return new ContactUserResource($item, $this->user);
        });
    }
}
