<?php

namespace App\Http\Resources\Group;

use App\Entities\Group;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GroupMessageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function($item){
            return new GroupMessageResource($item);
        });
    }
}
