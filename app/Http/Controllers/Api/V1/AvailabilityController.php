<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\AvailabilityGlobalSetting;
use App\Entities\UserDefWeekAvailability;
use App\Entities\UserWeekAvailability;
use App\Http\Controllers\Controller;
use App\Services\ProgressService;
use App\Services\StepService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = [];

        $now = Carbon::now();
        $currentWeek = $now->weekOfYear;
        $week = new \stdClass();
        $user = Auth::user();

        $defaultSetting = $user->defaultAvailability;
        $globalSetting = AvailabilityGlobalSetting::first();

        if ($defaultSetting) {
            $week->days = json_decode($defaultSetting->days, true);
            $week->is_fewer_time = $defaultSetting->is_fewer_time;
        } else {
            $week->days = json_decode($globalSetting->days, true);
            $week->is_fewer_time = $globalSetting->is_fewer_time;
        }

        $response['default_weeks'][] = $week;
        $response['weeks'] = UserWeekAvailability::where('week_no', '>=', $currentWeek)->where('user_id', $user->id)->orderBy('week_no', 'ASC')->take(3)->get(['id', 'text', 'days', 'is_fewer_time','week_no'])->each(function ($item) {
            $item->days = json_decode($item->days, true);
        });
        $response['filterable_id_list'] = json_decode($globalSetting->filterable_id_list, true);

        return $response;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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

    public function updateAvailability(Request $request)
    {
        $response = [];
        $user = Auth::user();
        $availability = UserWeekAvailability::where('user_id', $user->id)->where('id', $request['week_id'])->first();
        if($availability){
            $availability->days = json_encode($request['days']);
            $availability->is_fewer_time = $request['is_fewer_time'];
            if($availability->save()){
                $response['status'] = 'success';
                $response['message'] = 'Successfully update your availability';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something went wrong, try again';
        }

        return $response;
    }

    public function saveDefaultAvailability(Request $request)
    {
        $response = [];
        $user = Auth::user();
        $defAvailability = $user->defaultAvailability ?? new UserDefWeekAvailability();
        $defAvailability->user_id = $user->id;
        $defAvailability->is_fewer_time = $request['is_fewer_time'];
        $defAvailability->days = json_encode($request['days']);


        if ($defAvailability->save()) {
            $progressService= new ProgressService();
            $progress = $progressService->getUserAvailabilityPageProgress($user);
            $response['progress'] = $progress;

            if ($user->availabilities()->count() < 1) {
                $now = Carbon::now();
                $currentWeek = $now->weekOfYear;
                $weekStartDate = $now->startOfWeek();
                for ($i = 0; $i < 3; $i++) {
                    $availability = new UserWeekAvailability();
                    $availability->user_id = $user->id;
                    $availability->text = "Week " . $currentWeek;
                    $availability->week_no = $currentWeek;
                    $availability->week_start_date = $weekStartDate->format('Y-m-d');
                    $availability->days = $defAvailability->days;
                    $availability->is_fewer_time = $defAvailability->is_fewer_time;
                    $availability->save();

                    $weekStartDate->modify('+7 days');
                    $currentWeek = $weekStartDate->weekOfYear;
                }

            }
            $response['status'] = 'success';
            $response['message'] = 'Successfully saved your availability';
            $response['week_status'] = 'initial';
            $response['weeks'] = UserWeekAvailability::where('user_id', $user->id)->get(['id', 'text', 'days', 'is_fewer_time','week_no'])->each(function ($item) {
                $item->days = json_decode($item->days, true);
            });
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something went wrong, try again';
        }
        return $response;
    }
}
