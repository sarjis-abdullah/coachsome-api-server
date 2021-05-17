<?php


namespace App\Services;


use Carbon\Carbon;

class CalenderService
{
    public function getUserDayByDate($user, $date) {
        $searchedDay = null;
        $givenDate = Carbon::parse($date);
        $givenFormatedDate = $givenDate->format('Y-m-d');

        $availability = $user->availabilities->where('week_no', $givenDate->weekOfYear)->first();

        if($availability){
            $days = json_decode($availability->days,true);
            $weekStartDate = Carbon::parse($availability->week_start_date);
            $searchedDay = null;
            foreach ($days as $index=>$day) {
                if($givenFormatedDate == $weekStartDate->copy()->addDays($index)->format('Y-m-d')){
                    $searchedDay = $day;
                    break;
                }
            }
        }

        return $searchedDay;
    }
}
