<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\Distance;
use App\Http\Controllers\Controller;
use App\Services\ProgressService;
use App\Services\StepService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistanceController extends Controller {

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {

		$response = [];
		$writeMode = '';
		$user = Auth::user();

		if ($user->distance) {
			$writeMode = 'edit';
			$distance = $user->distance;
		} else {
			$writeMode = 'create';
			$distance = new Distance();
        }
        $distance->user_id = Auth::id();
        $distance->long = $request->long;
        $distance->lat = $request->lat;
        $distance->zip = $request->zip;
        $distance->city = $request->city;
        $distance->address = $request->address;
        $distance->far_away = $request->farAway;
        $distance->is_offer_only_online = $request->isOfferOnlyOnline;
        $distance->unit = $request->unit;
		if ($distance->save()) {
            $progressService= new ProgressService();
            $progress = $progressService->getUserGeographyPageProgress($user);

			$response['progress'] = $progress;
			$response['message'] = $writeMode == 'create' ? 'Successfully saved your distance' : 'Successfully update your distance';
			$response['status'] = 'success';
			return $response;
		} else {
			$response['status'] = 'error';
			$response['message'] = 'Something wrong, try again';
			return $response;
		}
	}

}
