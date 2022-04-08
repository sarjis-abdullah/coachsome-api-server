<?php

namespace App\Http\Controllers\Api\V1\Athlete;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\BookingTime;
use App\Http\Controllers\Controller;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $date = date('Y-m-d H:i:s',strtotime($request->date));

            $authUser = Auth::user();

            if (!$authUser) {
                throw new \Exception('User not found');
            }

            $bookingTimes = BookingTime::with(['location'])->where(function($q) use($authUser,$date){
                $q->orWhere('requester_user_id', $authUser->id);
                $q->orWhere('requester_to_user_id', $authUser->id);
            })
                ->where('calender_date','>=', $date)
                ->get()->map(function($item)  use($authUser){
                $address = '';
                $profileName = '';
                $date = '';
                $time = '';
                $isCoachToCoach = 0;
                $city = '';
                $zip = '';

                $date = date('d F',strtotime($item->calender_date));
                $time = date('h:i',strtotime($item->calender_date));

                $location = $item->location;
                $requesterUser = $item->requesterUser;
                $requesterToUser = $item->requesterToUser;
                $requesterUserProfile = $requesterUser ? $requesterUser->profile : null;
                $requesterUserRole = $requesterUser->roles()->first();
                $requesterToUserRole = $requesterToUser->roles()->first();

                if($location){
                    $address = $location->address;
                    $city = $location->city;
                    $zip = $location->zip;
                }

                if($authUser->id == $requesterUser->id){
                    $connectedUserProfile = $requesterToUser ? $requesterToUser->profile : null;
                    $connectedUser = $requesterToUser ? $requesterToUser : null;
                }

                if($authUser->id == $requesterToUser->id){
                    $connectedUserProfile = $requesterUser ? $requesterUser->profile : null;
                    $connectedUser = $requesterUser ? $requesterUser : null;
                }



                if($connectedUserProfile){
                    $profileName = $requesterUserProfile->profile_name;
                    $mediaService = new MediaService();
                    $profileImage = $mediaService->getImages($connectedUser);
                    $profileAvatarName = $connectedUserProfile->avatarName();
                }

                if($requesterUserRole->name == Constants::ROLE_KEY_COACH && $requesterToUserRole->name == Constants::ROLE_KEY_COACH){
                    $isCoachToCoach = 1;
                }


                return [
                    'bookingTimeId'=>$item->id,
                    'date' => $date,
                    'time' => $time,
                    'profileName'=>$profileName,
                    'profileImage' => $profileImage,
                    'profileAvatarName' => $profileAvatarName,
                    'status' => $item->status,
                    'address'=>$address,
                    'isCoachToCoach'=>$isCoachToCoach,
                    'city'=>$city,
                    'zip'=>$zip,
                ];
            })->take(6);



            return response()->json(['bookingTimes' => $bookingTimes], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
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
