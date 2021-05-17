<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

//        return [
//            'current_page' => $this->currentPage(),
//            'data' => $this->collection,
//            'first_page_url'=> $this->url(1),
//            'last_page_url'=> $this->url($this->lastPage()),
//            'total' => $this->total(),
//            'path'=> $this->resolveCurrentPath(),
//            'from' => $this->firstItem(),
//            'to' => $this->lastItem(),
//            'last_page' => $this->lastPage(),
//            'next_page_url' => $this->nextPageUrl(),
//            'per_page' => $this->perPage(),
//            'prev_page_url' => $this->previousPageUrl(),
//        ];
        
        return [
            'data' => $this->collection,
            'currentPage' => $this->currentPage(),
            'pageLength' => $this->lastPage(),
        ];
    }
}
