<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\StatusCode;
use App\Entities\BookingTime;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\CalenderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalenderController extends Controller
{
    public function getTimeSlotByDateRange(Request $request)
    {
        try {
            $date = $request->query('date');
            $userId = $request->query('userId');
            $session = $request->query('session');

            $user = User::find($userId);
            $calenderService = new CalenderService();

            if (!$user) {
                throw new \Exception('User not found');
            }

            if (!$date) {
                throw new \Exception('Date is missing');
            }

            $searchedDay = null;
            $givenDate = Carbon::parse($date);
            $givenFormattedDate = $givenDate->format('Y-m-d');

            $availability = $user->availabilities->where('week_no', $givenDate->weekOfYear)->first();

            if ($availability) {
                $days = json_decode($availability->days, true);
                $weekStartDate = Carbon::parse($availability->week_start_date);
                $searchedDay = null;
                foreach ($days as $index => $day) {
                    if ($givenFormattedDate == $weekStartDate->copy()->addDays($index)->format('Y-m-d')) {
                        $newTimeRanges = array_map(function($item) use($givenFormattedDate,$userId){
                            $formattedTimeRange =new \stdClass();
                            $formattedTimeRange->startTime = $item['start_time'];
                            $formattedTimeRange->endTime = $item['end_time'];

                            $bookingTimes = BookingTime::where('requester_to_user_id', $userId)
                                ->where('calender_date', $givenFormattedDate)
                                ->where('time_slot', json_encode($formattedTimeRange))
                                ->where('status', 'Accepted')
                                ->get();
                            $item['booked_times'] = $bookingTimes;

                            return $item;
                        },$day['time_ranges'] );
                        $day['time_ranges'] = $newTimeRanges;
                        $searchedDay = $day;
                        break;
                    }
                }
            }

            $day = $searchedDay;

            if (!$day) {
                throw new \Exception('No time slot found for this date. Try another one.');
            }


            return response()->json([
                'day' => $day,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
