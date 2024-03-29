<?php

namespace App\Http\Resources\Promo;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PromoDurationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            return new PromoDurationResource($item);
        });
    }
}
