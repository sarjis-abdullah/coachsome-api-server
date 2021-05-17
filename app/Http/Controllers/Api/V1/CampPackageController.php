<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\Package;
use App\Entities\PackageDetail;
use App\Http\Controllers\Controller;
use App\Services\StepService;
use App\Transformers\Package\PackagesTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class CampPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];

        $user = Auth::user();

        $package = new Package();
        $package->package_category_id = Constants::PACKAGE_CAMP_ID;
        $package->user_id = $user->id;
        $package->status = 0;
        if ($package->save()) {
            $packageDetail = new PackageDetail();
            $packageDetail->package_id = $package->id;
            $packageDetail->title = $request['title'];
            $packageDetail->description = $request['description'];
            $packageDetail->session = $request['session'];
            $packageDetail->time_per_session = $request['timePerSession'];
            $packageDetail->price = $request['price'];
            $packageDetail->attendees_min = $request['attendeesMin'];
            $packageDetail->attendees_max = $request['attendeesMax'];
            $packageDetail->completed_by_days = $request['completedByDays'];
            if ($packageDetail->save()) {

                // Change user page Step
                $stepService = new StepService();
                $stepService->manage($user, Constants::PAGE_KEY_PACKAGE);
                $progress = $stepService->stepInPercent($user, Constants::PAGE_KEY_PACKAGE);

                $resource = new Item($package, new PackagesTransformer($user));
                $fractalManager = new Manager();
                $response['package'] = $fractalManager->createData($resource);

                $response['status'] = 'success';
                $response['progress'] = $progress;
                $response['message'] = 'Successfully created your package';
            }
        }

        return $response;
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
