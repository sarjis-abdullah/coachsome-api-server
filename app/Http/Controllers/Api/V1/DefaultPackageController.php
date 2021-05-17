<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\Package;
use App\Entities\PackageCategory;
use App\Entities\PackageDetail;
use App\Http\Controllers\Controller;
use App\Serializer\CustomSerializer;
use App\Services\StepService;
use App\Transformers\Package\PackagesTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class DefaultPackageController extends Controller
{

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

        $package = new Package();
        $package->package_category_id = Constants::PACKAGE_DEFAULT_ID;
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
            $packageDetail->is_special_price = $request['isSpecialPrice'];
            $packageDetail->discount = $request['discount'];
            $packageDetail->transport_fee = $request['transportFee'];
            if ($packageDetail->save()) {

                // Change user page Step
                $stepService = new StepService();
                $stepService->manage($user, Constants::PAGE_KEY_PACKAGE);
                $progress = $stepService->stepInPercent($user, Constants::PAGE_KEY_PACKAGE);

                $resource = new Item($package, new PackagesTransformer($user));
                $fractalManager = new Manager();
                $fractalManager->setSerializer(new CustomSerializer());
                $response['package'] = $fractalManager->createData($resource);

                $response['status'] = 'success';
                $response['progress'] = $progress;
                $response['message'] = 'Successfully created your package';
            }
        }

        return $response;
    }

}
