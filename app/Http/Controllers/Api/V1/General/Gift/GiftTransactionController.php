<?php

namespace App\Http\Controllers\Api\V1\General\Gift;

use App\Data\Promo;
use App\Data\StatusCode;
use App\Data\TransactionType;
use App\Entities\GiftOrder;
use App\Entities\GiftTransaction;
use App\Entities\PromoCode;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiftTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $promoCode = PromoCode::where('code', $request['code'])
                ->where('promo_category_id',Promo::CATEGORY_ID_GIFT_CARD)
                ->first();

            if (!$promoCode) {
                throw new Exception('Promo code is not found');
            }

            $giftOrder = GiftOrder::where('promo_code_id', $promoCode->id)->first();
            if (!$giftOrder) {
                throw new Exception('Gift card is not found');
            }
            // Find the transacetion happened befor
            $searchedTransaction = GiftTransaction::where('gift_order_id', $giftOrder->id)
                ->where('type',TransactionType::DEBIT)
                ->first();
            if($searchedTransaction){
                throw new Exception('This code has already added');
            }

            // New transaction
            $giftTransaction = new GiftTransaction();
            $giftTransaction->user_id = Auth::id();
            $giftTransaction->gift_order_id = $giftOrder->id;
            $giftTransaction->transaction_date = Carbon::now();
            $giftTransaction->amount = $giftOrder->total_amount;
            $giftTransaction->currency = $giftOrder->currency;
            $giftTransaction->type = TransactionType::DEBIT;
            $giftTransaction->save();

            $balance = 0.00;
            $debitAmount = GiftTransaction::where('user_id', Auth::id())
                ->where('type', TransactionType::DEBIT)
                ->get()
                ->sum('amount');
            $creditAmount = GiftTransaction::where('user_id', Auth::id())
                ->where('type', TransactionType::CREDIT)
                ->get()
                ->sum('amount');
            $balance = $debitAmount - $creditAmount;

         
            return response([
                'data' => [
                    'balance' => $balance
                ]
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
