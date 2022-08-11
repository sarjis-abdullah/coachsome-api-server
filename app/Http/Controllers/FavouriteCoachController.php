<?php

namespace App\Http\Controllers;

use App\FavouriteCoach;
use App\Http\Requests\StoreFavouriteCoachRequest;
use App\Http\Requests\UpdateFavouriteCoachRequest;
use App\Http\Resources\FavouriteCoach\FavouriteCoachResource;
use App\Http\Resources\FavouriteCoach\FavouriteCoachResourceCollection;
use Illuminate\Support\Facades\Auth;

class FavouriteCoachController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $items = FavouriteCoach::with('coach')
            ->where('userId', "=",Auth::user()->id)
            ->where('isFavourite', "=", true)
            ->get();
        return new FavouriteCoachResourceCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreFavouriteCoachRequest $request
     * @return FavouriteCoachResource
     */
    public function store(StoreFavouriteCoachRequest $request): FavouriteCoachResource
    {
        $data = [];
        $request['userId'] = Auth::user()->id;
        $item = FavouriteCoach::where('userId', '=', $request['userId'])->first();
        if ($item){
            $item->isFavourite = false;
            $item->save();
            $data = $item;
        }else {
            $data['isFavourite'] = true;
            $fc = FavouriteCoach::create($request->all());
            $data = $fc;
        }
        return new FavouriteCoachResource($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\FavouriteCoach  $favouriteCoach
     * @return \Illuminate\Http\Response
     */
    public function show(FavouriteCoach $favouriteCoach)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFavouriteCoachRequest  $request
     * @param  \App\FavouriteCoach  $favouriteCoach
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFavouriteCoachRequest $request, FavouriteCoach $favouriteCoach)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FavouriteCoach  $favouriteCoach
     * @return \Illuminate\Http\Response
     */
    public function destroy(FavouriteCoach $favouriteCoach)
    {
        //
    }
}
