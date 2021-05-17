<?php

namespace App\Http\Resources\Profile;

use App\Http\Resources\Category\SportCategoryResource;
use App\Http\Resources\Review\ReviewResource;
use App\Http\Resources\Tag\SportTagResource;
use App\Services\Media\MediaService;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileCardResource extends JsonResource
{
    /**
     * @var
     */
    private $images;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @return void
     */
    public function __construct($resource, MediaService $mediaService)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;

        $this->images = $mediaService->getImages($this->user);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'userId' => $this->user->id ?? null,
            'profileName' => $this->profile_name,
            'image' => $this->images['square'] ? $this->images['square']: $this->images['old'],
            'tags' => SportTagResource::collection($this->user->sportTags),
            'categories' => SportCategoryResource::collection($this->user->sportCategories),
            'fbLink' => $this->social_acc_fb_link,
            'twitterLink' => $this->social_acc_twitter_link,
            'instagramLink' => $this->social_acc_instagram_link,
            'reviews' => ReviewResource::collection($this->user->reviews)
        ];
    }
}
