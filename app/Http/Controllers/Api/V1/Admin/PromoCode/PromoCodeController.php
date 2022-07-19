<?php

namespace App\Http\Controllers\Api\V1\Admin\PromoCode;

use App\Data\CurrencyCode;
use App\Data\OrderStatus;
use App\Data\StatusCode;
use App\Entities\Currency;
use App\Entities\GiftOrder;
use App\Entities\PromoCode;
use App\Entities\PromoDuration;
use App\Entities\PromoType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Currency\CurrencyCollection;
use App\Http\Resources\Promo\PromoCodeCollection;
use App\Http\Resources\Promo\PromoCodeResource;
use App\Http\Resources\Promo\PromoDurationCollection;
use App\Http\Resources\Promo\PromoTypeCollection;
use App\Services\TokenService;
use App\Utils\CurrencyUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $data['promoCodes'] = new PromoCodeCollection(PromoCode::orderBy('created_at', 'DESC')->get());
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
                'code' => [
                    'required',
                    Rule::unique('promo_codes'),
                ],
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
            if ($request['type'] == 3) {
                $promoCode->promo_category_id = 2;
                $order = new GiftOrder();
                $tokenService = new TokenService();
                $order->user_id = Auth::id();
                $order->promo_code_id = $promoCode->id;
                $order->currency = CurrencyCode::DANISH_KRONER;
                $order->total_amount = CurrencyUtil::convert(
                    $request['discount'],
                    $request->header('Currency-Code'),
                    CurrencyCode::DANISH_KRONER
                );
                $order->status = OrderStatus::INITIAL;
                $order->transaction_date = Carbon::now();
                $order->save();

                // $order->id only work when it saved
                $orderKey = $tokenService->getUniqueId('G');
                $order->key = $orderKey;
                $order->save();
            }
            $promoCode->save();

            return response([
                'promoCode' => new PromoCodeResource($promoCode)
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
        try {
            $request->validate([
                'id' => 'required',
                'name' => 'required',
                'code' => 'required',
                'currency' => 'nullable|numeric',
                'discount' => 'nullable|numeric',
                'percentageOff' => 'nullable|numeric',
                'type' => 'numeric',
                'duration' => 'numeric',
            ]);

            $promoCode = PromoCode::find($request['id']);
            if (!$promoCode) {
                throw new \Exception('PromoCode not found');
            }
            $promoCode->code = $request['code'];
            $promoCode->name = $request['name'];
            $promoCode->promo_type_id = $request['type'];
            $promoCode->promo_duration_id = $request['duration'];
            $promoCode->currency_id = $request['currency'];
            $promoCode->discount_amount = $request['discount'];
            $promoCode->percentage_off = $request['percentageOff'];
            $promoCode->save();

            return response([
                'promoCode' => new PromoCodeResource($promoCode)
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
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $promoCode = PromoCode::find($id);
            if (!$promoCode) {
                throw new \Exception('PromoCode not found');
            }
            $promoCode->delete();
            return response([], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
