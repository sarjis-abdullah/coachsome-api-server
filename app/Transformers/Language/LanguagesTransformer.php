<?php


namespace App\Transformers\Language;


use App\Data\Constants;
use App\Entities\Language;
use App\Services\TranslationService;
use League\Fractal;

class LanguagesTransformer extends Fractal\TransformerAbstract
{
    private $translations;

    public function __construct($languageCode = null)
    {
        $translationService = new TranslationService();
        if ($languageCode == Constants::LANGUAGE_DENAMARK_CODE) {
            $this->translations = $translationService->getKeyByLanguageCode($languageCode);
        } elseif ($languageCode == Constants::LANGUAGE_SWEDISH_CODE) {
            $this->translations = $translationService->getKeyByLanguageCode($languageCode);
        } elseif ($languageCode == Constants::LANGUAGE_USA_CODE) {
            $this->translations = $translationService->getKeyByLanguageCode($languageCode);
        } else {
            $this->translations = $translationService->getKeyByLanguageCode($languageCode);
        }
    }


    public function transform(Language $item)
    {
        return [
            'id' => $item->id,
            'name' => $this->translations[$item->t_key],
            't_key' => $item->t_key,
        ];

    }
}
