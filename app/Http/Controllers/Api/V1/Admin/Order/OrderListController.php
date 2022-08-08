<?php

namespace App\Http\Controllers\Api\V1\Admin\Order;

use App\Data\StatusCode;
use App\Entities\Booking;
use App\Entities\BookingTime;
use App\Http\Controllers\Controller;
use App\Http\Resources\Booking\BookingCollection;
use App\Http\Resources\Booking\SessionCollection;
use Illuminate\Http\Request;

class OrderListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       return new BookingCollection(Booking::orderBy('created_at','DESC')->paginate(9999999));
    }

    public function getSessionsData(Request $request){
        $booking_id = $request->booking_id;
        
        $sessions = BookingTime::with(['requesterUser','requesterToUser'])->where('booking_id', $booking_id)->where('status', 'Accepted')->get();

        $response['sessions'] = new SessionCollection($sessions);

        return response($response, StatusCode::HTTP_OK);

        // return [
        //     'data' => $sessions,
        // ];
    }
    public function removeSessionsData(Request $request){
        $session_id = $request->session_id;
        
        BookingTime::where('id', $session_id)->update(['status' => 'Removed By Admin' ]);

        $response['message'] = 'Session has been removed successfully';

        return response($response, StatusCode::HTTP_OK);
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
