<?php

namespace App\Http\Controllers\Api\V1\Athlete;

use App\Data\StatusCode;
use App\Entities\Booking;
use App\Http\Controllers\Controller;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $authUser = Auth::user();

        try {
            if (!$authUser) {
                throw new \Exception('User not found');
            }

            $storageService = new StorageService();


            $purchasedPackages = Booking::with(['order'])
                ->orderBy('is_favourite_to_package_owner','DESC')
                ->orderBy('is_favourite_to_package_buyer','DESC')
                ->where('package_buyer_user_id', $authUser->id)
                ->where(function ($q) {
                    $q->orWhere('status', 'Pending');
                    $q->orWhere('status', 'Accepted');
                })->paginate(6)->map(function ($item) use($authUser, $storageService) {

                    $packageTitle = '';
                    $profileName = '';
                    $totalSession = 0;
                    $leftSession = 0;
                    $profileImage = "";
                    $profileAvatarName = "";
                    $status = '';
                    $isSold = 1;
                    $date = '';
                    $packageDescription = '';
                    $isFavourite = 0;

                    $order = $item->order ? $item->order : null;
                    $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;
                    $packageOwnerUser = $item->packageOwnerUser ? $item->packageOwnerUser : null;
                    $packageBuyerUser = $item->packageBuyerUser ? $item->packageBuyerUser : null;
                    $profile = $packageOwnerUser ? $packageOwnerUser->profile : null;
                    $packageDetails = $packageSnapshot ? $packageSnapshot->details : null;
                    $bookingTimeCount = $item->bookingTimes->where('status','Accepted')->count();

                    $status = $item->status;
                    $date = date('d/m',strtotime($item->booking_date));

                    if($item->package_owner_user_id == $authUser->id){
                        $isSold = 1;
                        $isFavourite = $item->is_favourite_to_package_owner;
                    } else {
                        $isSold = 0;
                        $isFavourite = $item->is_favourite_to_package_buyer;
                    }

                    if($profile){
                        $profileName = $profile->profile_name;
                        $profileImage = $profile->image && $storageService->hasImage($profile->image)
                            ? $profile->image
                            : "";
                        $profileAvatarName = $profile->avatarName();
                    }

                    if($packageDetails){
                        $packageTitle = $packageDetails->title;
                        $packageDescription = $packageDetails->description;
                        $totalSession = $packageDetails->session;
                        $leftSession = $totalSession - $bookingTimeCount;
                    }

                    return [
                        'bookingId'=> $item->id,
                        'packageOwnerUserId'=> $packageOwnerUser->id,
                        'packageBuyerUserId'=> $packageBuyerUser->id,
                        'profileAvatarName' => $profileAvatarName,
                        'packageTitle' => $packageTitle,
                        'packageDescription' => $packageDescription,
                        'profileImage' => $profileImage,
                        'profileName' => $profileName,
                        'totalSession' => $totalSession,
                        'leftSession' => $leftSession,
                        'status' => $status,
                        'date' => $date,
                        'isSold' => $isSold,
                        'isFavourite' => $isFavourite
                    ];
                });

            return response()->json(['purchasedPackages' => $purchasedPackages], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function changeFavourite(Request $request)
    {
        try {
            $bookingId = $request->bookingId;

            $isFavourite = 0;

            $booking = Booking::find($bookingId);
            $authUser = Auth::user();

            if(!$booking){
                throw new \Exception('Booking not found');
            }

            if($authUser->id != $booking->package_buyer_user_id){
                throw new \Exception('You are not permitted this action.');
            }


            $isFavourite = !$booking->is_favourite_to_package_buyer;
            $booking->is_favourite_to_package_buyer = $isFavourite;
            $booking->save();

            return response()->json([
                'message'=>'Successfully change your favour',
                'isFavourite'=>$isFavourite],
                StatusCode::HTTP_OK
            );
        } catch (\Exception $e) {
            return response()->json([
                'message'=> $e->getMessage(),
            ],
                StatusCode::HTTP_UNPROCESSABLE_ENTITY
            );
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
