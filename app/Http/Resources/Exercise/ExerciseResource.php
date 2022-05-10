<?php

namespace App\Http\Resources\Exercise;

use App\Data\ExerciseData;
use App\Entities\ExerciseAsset;
use App\Entities\SportCategory;
use App\Services\Media\MediaService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

class ExerciseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $mediaService = new MediaService();
        $assets = ExerciseAsset::whereIn('id', explode(',',$this->exercise_asset_ids))->orderBy('sort', 'asc')->get()->map(function ($item) use ($mediaService) {
            $url = $item->url ?? '';
            if ($item->type == 'image') {
                $url = $mediaService->getGalleryImageUrl($item->file_name);
            }
            return [
                'id' => $item->id,
                'type' => $item->type,
                'url' => $url,
            ];
        })->values();
        // dd( $assets);

        $name = $this->name;
        $instructions = $this->instructions;

        $CategoryData= collect(Config::get('exercise.exercise_categories'));
        $filteredCategoryData = $CategoryData->where('id',$this->category_id);
        $category = $filteredCategoryData->all();



        $sport = SportCategory::where('id', $this->sport_id)->first();


        $LavelData= collect(Config::get('exercise.exercise_categories'));
        $filteredLavelData = $LavelData->where('id',$this->lavel_id);
        $lavel = $filteredLavelData->all();

        $tags = explode(',', $this->tags);

        $id = $this->id;
        

        $type = $this->type == 1 ? ExerciseData::EXERCISE_TYPE_SYSTEM : ExerciseData::EXERCISE_TYPE_CUSTOM;

        return [
            'id' => $id,
            'name' => $name,
            'assets' => $assets,
            'instructions' => $instructions,
            'category' => $category,
            'sport' => $sport,
            'lavel' => $lavel,
            'tags' => $tags,
            'type' => $type
        ];
    }
}
