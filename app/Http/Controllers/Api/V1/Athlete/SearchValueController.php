<?php

namespace App\Http\Controllers\Api\V1\Athlete;

use App\Data\StatusCode;
use App\Entities\SearchValue;
use App\Entities\SportCategory;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchValueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            $type = $request->type;

            $searchValues = [];
            $authUser = Auth::user();

            if($type == 'category'){
                 $todaySearchValues = SearchValue::where('user_id', $authUser->id)
                    ->where('date', Carbon::now()->format('Y-m-d'))
                     ->where('type', $type)
                    ->first();
                 if($todaySearchValues){
                     $searchValues['today'] = SportCategory::whereIn('id', json_decode($todaySearchValues->value, true))->get();
                 } else {
                     $searchValues['today'] = [];
                  }

                 $tomorrowSearchValues = SearchValue::where('user_id', $authUser->id)
                     ->where('date', Carbon::now()->subDay()->format('Y-m-d'))
                     ->where('type', $type)
                     ->first();

                 if($tomorrowSearchValues){
                     $searchValues['yesterday'] = SportCategory::whereIn('id', json_decode($tomorrowSearchValues->value, true))->get();
                 } else {
                     $searchValues['yesterday'] = [];
                 }

                $weekSearchValues = SearchValue::where('user_id', $authUser->id)
                    ->where('date', Carbon::now()->subDays(7)->format('Y-m-d'))
                    ->where('type', $type)
                    ->first();
                if($weekSearchValues){
                    $searchValues['week'] = SportCategory::whereIn('id', json_decode($weekSearchValues->value, true))->get();
                } else {
                    $searchValues['week'] = [];
                }


                $laterSearchValues = SearchValue::where('user_id', $authUser->id)
                    ->where('date', '<=',Carbon::now()->subDays(8)->format('Y-m-d'))
                    ->where('type', $type)
                    ->get();
                if($laterSearchValues->count() > 0){
                    $categoryIdList = [];
                    foreach ($laterSearchValues as $laterSearchValue) {
                        $categoryIdList =  array_merge($categoryIdList, json_decode($laterSearchValue->value, true));
                    }
                    $searchValues['later'] = SportCategory::whereIn('id', $categoryIdList)->get();
                } else {
                    $searchValues['later'] = [];
                }

            } else {

            }

            return response()->json([
                'message' => '',
                'searchValues'=>$searchValues,
            ], StatusCode::HTTP_OK);
        }catch (\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
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
