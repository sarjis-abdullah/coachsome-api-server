<?php


namespace App\Transformers\Profile;

use App\Entities\Profile;
use League\Fractal;

class ProfileTransformer extends Fractal\TransformerAbstract
{
    public function transform(Profile $item)
    {
        return [
            'id' => $item->id,
            'image' => $item->getImage(),
        ];
    }
}
