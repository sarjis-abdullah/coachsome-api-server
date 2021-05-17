<?php

namespace App\Http\Controllers\Api\V1\Coach;

use App\Data\StatusCode;
use App\Entities\PayoutRequest;
use App\Http\Controllers\Controller;
use App\Services\BalanceEarningService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PayoutRequestController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function doRequest(Request $request)
    {
        try {
            $userCurrency = $request->header('Currency-Code');
            $authUser = Auth::user();
            $balanceEarningService = new BalanceEarningService();

            $result = $balanceEarningService->getUserBalanceEarningInfo($authUser, $userCurrency);

            $currentBalanceAmount = $result['currentBalance']['amount'];
            $currentBalanceCurrency = $result['currentBalance']['currency'];

            if ($currentBalanceAmount <= 0) {
                throw new \Exception('Sorry! you have no balance.');
            }

            $todayDate = Carbon::now();

            $payout = new PayoutRequest();
            $payout->user_id = $authUser->id;
            $payout->amount = $currentBalanceAmount;
            $payout->currency = $userCurrency;
            $payout->date_with_time = date('Y-m-d H:i:s', strtotime($todayDate));
            $payout->date = date('Y-m-d', strtotime($todayDate));
            $payout->status = 'Pending';
            $payout->save();

            Mail::to(config('mail.from.address'))->send(new \App\Mail\PayoutRequest($authUser));


            return response()->json([
                'currentBalanceAmount' => $currentBalanceAmount,
                'currentBalanceCurrency' => $currentBalanceCurrency,
                'requestTime' => date('Y-m-d', strtotime($todayDate)),
                'message' =>'Your request is successfully created.'
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


}
