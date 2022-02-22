<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\CurrencyCode;
use App\Data\StatusCode;
use App\Entities\Currency;
use App\Entities\PaymentCard;
use App\Http\Controllers\Controller;
use App\Services\QuickpayCardService;
use App\Services\QuickpayClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentCardController extends Controller
{

    public function index(Request $request)
    {
        try {
            $card = null;
            $quickpayCardService = new QuickpayCardService();
            $paymentCard = PaymentCard::where('user_id', Auth::id())->first();
            if ($paymentCard) {
                $response = $quickpayCardService->getQuickPayCard($paymentCard->card_id);
                // Check Not authorized, Not found status
                if ($response->httpStatus() == 404 || $response->httpStatus() == 403) {
                    PaymentCard::where('user_id', Auth::id())->delete();
                } else {
                    $resObj = $response->asObject();
                    if ($resObj) {
                        if (!$resObj->accepted) {
                            PaymentCard::where('user_id', Auth::id())->delete();
                        } else {
                            $card = $response->asObject();
                        }

                    }
                }
            }
            return response([
                'data' => $card,
            ], StatusCode::HTTP_OK);

        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            return response([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(Request $request)
    {

        try {
            $request->validate([
                'continueUrl' => 'required',
                'cancelUrl' => 'required',
            ]);
            $link = "";
            $authUser = Auth::user();
            $quickpayCardService = new QuickpayCardService();
            $card = $quickpayCardService->createQuickPayCard();
            if ($card) {
                $link = $quickpayCardService->getQuickPayCardLink($card->id, $request['continueUrl'], $request['cancelUrl']);
            } else {
                throw new \Exception("Card is not created. Something went wrong ...");
            }

            $paymentCard = PaymentCard::where('user_id', $authUser->id)->first();
            if (!$paymentCard) {
                $paymentCard = new PaymentCard();
            }
            $paymentCard->user_id = $authUser->id;
            $paymentCard->card_id = $card->id;
            $paymentCard->link = $link;
            $paymentCard->save();
            return response([
                'data' => $link,
            ], StatusCode::HTTP_OK);

        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            return response([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function cancel(Request $request)
    {

        try {

            $quickpayCardService = new QuickpayCardService();
            $paymentCard = PaymentCard::where('user_id', Auth::id())->first();
            if (!$paymentCard) {
                throw new \Exception("Card is not found");
            }

            $response = $quickpayCardService->cancelQuickPayCard($paymentCard->card_id);
            $status = $response->httpStatus();

            if ($status == 202) {
                $paymentCard->delete();
            }

            return response([
                'data' => [],
            ], StatusCode::HTTP_OK);

        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(
                    $e->validator->errors()->first(),
                    StatusCode::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            return response([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
