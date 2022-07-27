<?php

namespace App\Http\Resources\Exercise;

use App\Data\ExerciseData;
use App\Entities\ExerciseAsset;
use App\Entities\ExerciseSportCategory;
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

        $assets = [];

        if($this->exercise_asset_ids != null){

            $assets = ExerciseAsset::whereIn('id', explode(',',$this->exercise_asset_ids))->orderBy('sort', 'asc')->get()->map(function ($item) use ($mediaService) {
                $url = $item->url ?? '';
                if ($item->type == 'image') {
                    $url = $mediaService->getGalleryImageUrl($item->file_name);
                }
                if ($item->type == 'custom-video') {
                    $url = $mediaService->getExerciseVideoUrl($item->file_name);
                }
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'url' => $url,
                    'url_type' => 'stored'
                ];
            })->values();

        }else if(!isset($this->show_default_image)){

            $assets[] = array(
                'id' => 1,
                'type' => "image",
                'url' => env('APP_SERVER_DOMAIN_STORAGE_PATH') . '/assets/images/exercise_default.jpg',
                'url_type' => 'default'
            );
        }

        $name = $this->name;
        $instructions = $this->instructions;

        $CategoryData= collect(Config::get('exercise.exercise_categories'));
        $filteredCategoryData = $CategoryData->whereIn('id', explode(',', $this->category_id));
        $category = $filteredCategoryData->all();

        $SportData = collect(ExerciseSportCategory::orderBy('id', 'asc')->get());
        $filteredSportData = $SportData->whereIn('id',  explode(',', $this->sport_id));
        $sport = $filteredSportData->all();


        $LavelData= collect(Config::get('exercise.exercise_lavels'));
        $filteredLavelData = $LavelData->whereIn('id', explode(',', $this->lavel_id));
        $lavel = $filteredLavelData->all();

        $tags =  $this->tags == "" ? [] : explode(',', $this->tags);

        $id = $this->id;
        $share_with_coach = $this->share_with_coach;
        

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
            'type' => $type,
            'shareWithCoach' => $share_with_coach,
        ];
    }
}
