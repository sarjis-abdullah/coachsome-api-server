<?php

namespace App\Http\Controllers;

use App\FavouriteCoach;
use App\Http\Requests\DeleteFavouriteCoachRequest;
use App\Http\Requests\StoreFavouriteCoachRequest;
use App\Http\Requests\UpdateFavouriteCoachRequest;
use App\Http\Resources\FavouriteCoach\FavouriteCoachResource;
use App\Http\Resources\FavouriteCoach\FavouriteCoachResourceCollection;
use Illuminate\Http\Response;
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
//            ->where('isFavourite', "=", true)
            ->get();
        return new FavouriteCoachResourceCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreFavouriteCoachRequest $request
     * @return FavouriteCoachResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function store(StoreFavouriteCoachRequest $request)
    {
        $request['userId'] = Auth::user()->id;
        if (!$request['isFavourite']){
                $fc = FavouriteCoach::where('coachId', '=', $request['coachId'])
                    ->where('userId', '=', $request['userId'])
                    ->first();
                if (!$fc){
                    return response([
                        'message' => "Not found!"
                    ], 404);
                }
                $fc->forceDelete();
                return response([
                    'message' => 'Deleted successfully!'
                ], 200);
        }else {
            $request['isFavourite'] = true;
            $fc = FavouriteCoach::create($request->all());
            return new FavouriteCoachResource($fc);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param FavouriteCoach $favouriteCoach
     * @return Response
     */
    public function show(FavouriteCoach $favouriteCoach)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFavouriteCoachRequest  $request
     * @param FavouriteCoach $favouriteCoach
     * @return Response
     */
    public function update(UpdateFavouriteCoachRequest $request, FavouriteCoach $favouriteCoach)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteFavouriteCoachRequest $request
     * @return Response
     */
    public function destroy(DeleteFavouriteCoachRequest $request): Response
    {
        if (!$request['isFavourite']) {
            $fc = FavouriteCoach::where('coachId', '=', $request['coachId'])
                ->where('userId', '=', Auth::user()->id)
                ->first();
            $fc->forceDelete();
            return response([
                'message' => 'Deleted successfully!'
            ], 200);
        }
        return response([
            'message' => 'Not found!'
        ], 404);
    }
}
