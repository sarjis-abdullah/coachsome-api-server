<?php


namespace App\Http\Controllers\Api\V1\Admin\Translation;


use App\Data\StatusCode;
use App\Data\TranslationData;
use App\Entities\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SeoTranslationController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $profilePageMetaTitleTranslation = Translation::where('gl_key', TranslationData::PROFILE_PAGE_META_TITLE_KEY)->first();
        $profilePageMetaDescriptionTranslation = Translation::where('gl_key', TranslationData::PROFILE_PAGE_META_DESCRIPTION_KEY)->first();

        $homePageMetaTitleTranslation = Translation::where('gl_key', TranslationData::HOME_PAGE_META_TITLE_KEY)->first();
        $homePageMetaDescriptionTranslation = Translation::where('gl_key', TranslationData::HOME_PAGE_META_DESCRIPTION_KEY)->first();

        $marketplacePageMetaTitleTranslation = Translation::where('gl_key', TranslationData::MARKETPLACE_PAGE_META_TITLE_KEY)->first();
        $marketplaceMetaDescriptionTranslation = Translation::where('gl_key', TranslationData::MARKETPLACE_PAGE_META_DESCRIPTION_KEY)->first();

        $data = [
            'profilePage' => [
                'metaTitle' => [
                    'enValue' => $profilePageMetaTitleTranslation->en_value ?? '',
                    'dnValue' => $profilePageMetaTitleTranslation->dn_value ?? '',
                    'svValue' => $profilePageMetaTitleTranslation->sv_value ?? '',
                ],
                'metaDescription' => [
                    'enValue' => $profilePageMetaDescriptionTranslation->en_value ?? '',
                    'dnValue' => $profilePageMetaDescriptionTranslation->dn_value ?? '',
                    'svValue' => $profilePageMetaDescriptionTranslation->sv_value ?? '',
                ],
            ],
            'marketplacePage' => [
                'metaTitle' => [
                    'enValue' => $marketplacePageMetaTitleTranslation->en_value ?? '',
                    'dnValue' => $marketplacePageMetaTitleTranslation->dn_value ?? '',
                    'svValue' => $marketplacePageMetaTitleTranslation->sv_value ?? '',
                ],
                'metaDescription' => [
                    'enValue' => $marketplaceMetaDescriptionTranslation->en_value ?? '',
                    'dnValue' => $marketplaceMetaDescriptionTranslation->dn_value ?? '',
                    'svValue' => $marketplaceMetaDescriptionTranslation->sv_value ?? '',
                ],
            ],
            'homePage' => [
                'metaTitle' => [
                    'enValue' => $homePageMetaTitleTranslation->en_value ?? '',
                    'dnValue' => $homePageMetaTitleTranslation->dn_value ?? '',
                    'svValue' => $homePageMetaTitleTranslation->sv_value ?? '',
                ],
                'metaDescription' => [
                    'enValue' => $homePageMetaDescriptionTranslation->en_value ?? '',
                    'dnValue' => $homePageMetaDescriptionTranslation->dn_value ?? '',
                    'svValue' => $homePageMetaDescriptionTranslation->sv_value ?? '',
                ],
            ],
        ];

        return response([
            'data' => $data,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        try {
            $translation = null;

            // Profile
            if ($request->page == 'profile' && $request->field == 'meta_title') {
                $translation = Translation::where('gl_key', TranslationData::PROFILE_PAGE_META_TITLE_KEY)->first() ?? new Translation();
                $translation->gl_key = TranslationData::PROFILE_PAGE_META_TITLE_KEY;
                $translation->en_value = $request->enValue;
                $translation->dn_value = $request->dnValue;
                $translation->sv_value = $request->svValue;
                $translation->save();
            }

            if ($request->page == 'profile' && $request->field == 'meta_description') {
                $translation = Translation::where('gl_key', TranslationData::PROFILE_PAGE_META_DESCRIPTION_KEY)->first() ?? new Translation();
                $translation->gl_key = TranslationData::PROFILE_PAGE_META_DESCRIPTION_KEY;
                $translation->en_value = $request->enValue;
                $translation->dn_value = $request->dnValue;
                $translation->sv_value = $request->svValue;
                $translation->save();
            }

            // Home
            if ($request->page == 'home' && $request->field == 'meta_title') {
                $translation = Translation::where('gl_key', TranslationData::HOME_PAGE_META_TITLE_KEY)->first() ?? new Translation();
                $translation->gl_key = TranslationData::HOME_PAGE_META_TITLE_KEY;
                $translation->en_value = $request->enValue;
                $translation->dn_value = $request->dnValue;
                $translation->sv_value = $request->svValue;
                $translation->save();
            }

            if ($request->page == 'home' && $request->field == 'meta_description') {
                $translation = Translation::where('gl_key', TranslationData::HOME_PAGE_META_DESCRIPTION_KEY)->first() ?? new Translation();
                $translation->gl_key = TranslationData::HOME_PAGE_META_DESCRIPTION_KEY;
                $translation->en_value = $request->enValue;
                $translation->dn_value = $request->dnValue;
                $translation->sv_value = $request->svValue;
                $translation->save();
            }

            // Marketplace
            if ($request->page == 'marketplace' && $request->field == 'meta_title') {
                $translation = Translation::where('gl_key', TranslationData::MARKETPLACE_PAGE_META_TITLE_KEY)->first() ?? new Translation();
                $translation->gl_key = TranslationData::MARKETPLACE_PAGE_META_TITLE_KEY;
                $translation->en_value = $request->enValue;
                $translation->dn_value = $request->dnValue;
                $translation->sv_value = $request->svValue;
                $translation->save();
            }

            if ($request->page == 'marketplace' && $request->field == 'meta_description') {
                $translation = Translation::where('gl_key', TranslationData::MARKETPLACE_PAGE_META_DESCRIPTION_KEY)->first() ?? new Translation();
                $translation->gl_key = TranslationData::MARKETPLACE_PAGE_META_DESCRIPTION_KEY;
                $translation->en_value = $request->enValue;
                $translation->dn_value = $request->dnValue;
                $translation->sv_value = $request->svValue;
                $translation->save();
            }


            return response([
                'data' => $translation,
                'message' => 'The item is created successfully.'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Translation $translation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $translation = Translation::find($request->id);
            $translation->gl_key = $request->gl_key;
            $translation->status = $request->status;
            $translation->en_value = $request->enValue;
            $translation->dn_value = $request->dnValue;
            $translation->sv_value = $request->svValue;
            $translation->page_name = $request->page_name;
            $translation->group = $request->group;
            $translation->save();
            return response([
                'data' => $translation
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}
