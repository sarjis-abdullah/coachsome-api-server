<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
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
            'userId' => $this->user_id,
            'categoryId' => $this->package_category_id,
            'status' => $this->status,
            'category' => new PackageCategoryResource($this->category),
            'details' => new PackageDetailsResource($this->details)
        ];
    }
}
