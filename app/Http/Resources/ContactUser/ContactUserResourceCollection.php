<?php

namespace App\Http\Resources\ContactUser;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactUserResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable
     */
    public function toArray($request) : array
    {
        return parent::toArray($request);
    }
}
