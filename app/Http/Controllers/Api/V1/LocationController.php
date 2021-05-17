<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\Location;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\Country\CountryService;
use App\Services\ProgressService;
use App\Services\StepService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $userId = $request->query('userId');

            $user = null;

            if ($userId) {
                $user = User::find($userId);
            } else {
                $user = Auth::user();
            }

            if (!$user) {
                new \Exception('User not found');
            }

            $locations = Location::where(
                'user_id', $user->id
            )->get([
                'id',
                'lat',
                'long',
                'city',
                'address',
                'zip',
                'cca2'
            ]);

            return response()->json([
                'status' => 'success',
                'locations' => $locations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
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
        $response = [];

        $user = Auth::user();
        $location = new Location();

        $locale = App::currentLocale();
        $countryService = new CountryService();
        $countryList = $countryService->getCountryList($locale);

        $location->user_id = $user->id;
        $location->long = $request->long;
        $location->lat = $request->lat;
        $location->zip = $request->zip;
        $location->address = $request->address;
        $location->city = $request->city;
        $location->cca2 = $request->cca2;
        $location->google_map_api_response = $request->googleMapApiResponse ? json_encode($request->googleMapApiResponse) : "";

        if ($location->save()) {
            $progressService = new ProgressService();
            $progress = $progressService->getUserGeographyPageProgress($user);
            $response['progress'] = $progress;
            $response['status'] = 'success';
            $response['message'] = 'Successfully saved your location';
            $response['location'] = $location;

            // Country name formatting
            if ($location->cca2) {
                $location->cca2 = $countryList[$location->cca2];
            }
            return $response;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something wrong, try again';
            return $response;
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
        $response = [];
        $location = Location::where('user_id', Auth::id())->where('id', $id)->first();
        if ($location) {
            if ($location->delete()) {
                $response['status'] = 'success';
                $response['message'] = 'Location removed successfully';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Location do not find';
        }
        return $response;
    }
}
