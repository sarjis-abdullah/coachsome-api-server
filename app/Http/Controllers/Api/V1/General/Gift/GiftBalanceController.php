<?php

namespace App\Http\Controllers\Api\V1\General\Gift;

use App\Data\StatusCode;
use App\Data\TransactionType;
use App\Entities\GiftTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiftBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
