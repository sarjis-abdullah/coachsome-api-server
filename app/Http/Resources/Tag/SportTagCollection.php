<?php

namespace App\Http\Resources\Tag;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SportTagCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($sportTag){
            return [
                'id' => $sportTag->id,
                'name' => $sportTag->name,
            ];
        });
    }
}
