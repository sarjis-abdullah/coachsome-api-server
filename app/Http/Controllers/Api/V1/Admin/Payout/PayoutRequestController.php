<?php

namespace App\Http\Controllers\Api\V1\Admin\Payout;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\PayoutRequest;
use App\Http\Controllers\Controller;
use App\Services\BalanceEarningService;
use App\Services\Media\MediaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PayoutRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $mediaService = new MediaService();
            $payoutRequests = PayoutRequest::orderBy('date', 'DESC')->with('user')->get()->map(function ($item) use($mediaService) {
                $userImage = null;
                $user = $item->user;
                $images = $mediaService->getImages($user);

                if($images['square']){
                    $userImage = $images['square'];
                } else {
                    $userImage = $images['old'];
                }


                return [
                    'id' => $item->id,
                    'image' => $userImage,
                    'name' => $user ? $user->first_name . ' ' . $user->last_name : '',
                    'email' => $user ? $user->email : '',
                    'userId' => $user ? $user->id : '',
                    'amount' => $item->amount,
                    'currency' => $item->currency,
                    'requestTime' => date('d-m-Y',strtotime($item->date)),
                    'status' => $item->status

                ];
            });

            return response()->json(['payoutRequests' => $payoutRequests], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function paid(Request $request)
    {
        try {

            $authUser = Auth::user();
            $payoutRequestId = $request->payoutRequestId;

            if(!$authUser->hasRole([Constants::ROLE_KEY_SUPER_ADMIN, Constants::ROLE_KEY_ADMIN, Constants::ROLE_KEY_STAFF])){
                throw new \Exception('You have no permission for this action.');
            }
            $payoutRequest = PayoutRequest::find($payoutRequestId);
            $payoutRequest->status = 'Paid';
            $payoutRequest->save();

            return response()->json([
                'payoutRequest'=> $payoutRequest,
                'message' => 'Successfully paid of the request'
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


}
