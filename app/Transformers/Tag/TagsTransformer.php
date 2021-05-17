<?php


namespace App\Transformers\Tag;

use App\Entities\SportTag;
use App\Services\StorageService;
use League\Fractal;

class TagsTransformer extends Fractal\TransformerAbstract
{
    private $storageService;

    public function __construct()
    {
        $this->storageService = new StorageService();
    }

    public function transform(SportTag $item)
    {
        return [
            'id' => $item->id,
            'image' => $this->storageService->hasImage($item->image) ? $item->image : null,
        ];
    }
}
