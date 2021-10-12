<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\ChatSettingData;
use App\Data\StatusCode;
use App\Entities\ChatSetting;
use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\ChatSettingResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatSettingController extends Controller
{
    public function index(Request $request)
    {
        $chatSetting = ChatSetting::where('user_id', Auth::id())->first();
        return response()->json(['data' => $chatSetting ? new ChatSettingResource($chatSetting) : []], StatusCode::HTTP_OK);
    }


    public function enterPress(Request $request)
    {

        try {

            $value = $request->value;
            if (!$value) {
                throw new \Exception('No value found');
            }

            $chatSetting = ChatSetting::where('user_id', Auth::id())->first();
            if (!$chatSetting) {
                $chatSetting = new ChatSetting();
                $chatSetting->user_id = Auth::id();
            }
            if ($value == ChatSettingData::ENTER_PRESS_LINE_BREAK) {
                $chatSetting->enter_press = $value;
            }
            if ($value == ChatSettingData::ENTER_PRESS_SEND_MESSAGE) {
                $chatSetting->enter_press = $value;
            }
            $chatSetting->save();
            return response()->json(['data'=> new ChatSettingResource($chatSetting)], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['errors' => [$e->getMessage()]], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
