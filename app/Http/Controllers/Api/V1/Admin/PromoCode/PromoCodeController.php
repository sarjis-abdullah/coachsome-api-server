<?php

namespace App\Http\Controllers\Api\V1\Admin\PromoCode;

use App\Data\StatusCode;
use App\Entities\Currency;
use App\Entities\PromoCode;
use App\Entities\PromoDuration;
use App\Entities\PromoType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Currency\CurrencyCollection;
use App\Http\Resources\Promo\PromoCodeResource;
use App\Http\Resources\Promo\PromoDurationCollection;
use App\Http\Resources\Promo\PromoTypeCollection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PromoCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            "promoCodes" => [],
            "currencies" => [],
            "durations" => [],
            "types" => []
        ];
        try {
            $data['currencies'] = new CurrencyCollection(Currency::all());
            $data['durations'] = new PromoDurationCollection(PromoDuration::all());
            $data['types'] = new PromoTypeCollection(PromoType::all());
            return response($data, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'code' => 'required',
                'currency' => 'nullable|numeric',
                'discount' => 'nullable|numeric',
                'percentageOff' => 'nullable|numeric',
                'type' => 'numeric',
                'duration' => 'numeric',
            ]);
            $promoCode = new PromoCode();
            $promoCode->code = $request['code'];
            $promoCode->name = $request['name'];
            $promoCode->promo_type_id = $request['type'];
            $promoCode->promo_duration_id = $request['duration'];
            $promoCode->currency_id = $request['currency'];
            $promoCode->discount_amount = $request['discount'];
            $promoCode->percentage_off = $request['percentageOff'];
            $promoCode->save();

            return response([
                'promoCode'=> new PromoCodeResource($promoCode)
            ], StatusCode::HTTP_OK);


        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => $e->validator->errors()->first()
                ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
