<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'provider'=>$this->provider,
            'pageId'=>$this->page_id,
            'rating'=>$this->overall_star_rating,
            'ratingCount'=>$this->rating_count,
            'reviewers'=> json_decode($this->reviewers,true),
        ];
    }
}
