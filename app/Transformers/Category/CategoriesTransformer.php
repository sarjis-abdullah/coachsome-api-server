<?php


namespace App\Transformers\Category;


use App\Data\Constants;
use App\Entities\Currency;
use App\Entities\SportCategory;
use App\Entities\Translation;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Log;
use League\Fractal;

class CategoriesTransformer extends Fractal\TransformerAbstract
{
    private $translations;

    public function __construct($languageCode = null)
    {
        $translationService = new TranslationService();
        if ($languageCode == Constants::LANGUAGE_DENAMARK_CODE) {
            $this->translations = $translationService->getKeyByLanguageCode($languageCode);
        } elseif ($languageCode == Constants::LANGUAGE_USA_CODE) {
            $this->translations = $translationService->getKeyByLanguageCode($languageCode);
        } else {
            $this->translations = $translationService->getKeyByLanguageCode(Constants::LANGUAGE_USA_CODE);
        }
    }

    public function transform(SportCategory $item)
    {

        return [
            'id' => $item->id,
            'name' => $this->translations[$item->t_key],
            't_key' => $item->t_key,
            'priority' => $item->priority,
            'image' => $item->image,
        ];

    }
}
