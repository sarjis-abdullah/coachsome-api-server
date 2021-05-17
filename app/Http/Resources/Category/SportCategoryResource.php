<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Resources\Json\JsonResource;

class SportCategoryResource extends JsonResource
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
            'name' => $this->name,
            't_key' => $this->t_key,
            'priority' => $this->priority,
            'image' => $this->image_file_name ? asset("assets/images/category/" . $this->image_file_name) : null,
            'isImageFullUrl' => $this->is_image_full_url,
        ];
    }
}
