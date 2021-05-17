<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\Package;
use App\Entities\PackageDetail;
use App\Entities\PackageUserSetting;
use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use App\Services\ProgressService;
use App\Services\StepService;
use App\Services\TransformerService;
use App\Transformers\Package\PackagesTransformer;
use App\Transformers\Package\PackageTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Auth;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = [];

        $user = Auth::user();

        // Hourly rate
        $response['hourly_rate'] = $user->ownPackageSetting->hourly_rate ?? 0.00;

        // Quick booking
        $response['quickBooking'] = $user->ownPackageSetting->is_quick_booking ?? false;

        // Packages
        $response['packages'] = $this->getUserPackages($user);

        // Currency
        $currencyService = new CurrencyService();
        $userCurrency = $currencyService->getUserCurrency($user);
        $response['currency_code'] = $userCurrency ? $userCurrency->code : null;

        $response['status'] = 'success';

        return $response;
    }

    public function getUserPackages($user)
    {
        $resource = new Collection($user->packages()->orderBy('order')->get(), new PackagesTransformer($user));
        $fractalManager = new Manager();
        return $fractalManager->createData($resource);
    }

    public function updateOrder(Request $request)
    {
        $idList = $request->all();
        if (!empty($idList)) {
            $authUser = Auth::user();
            foreach ($idList as $i => $id) {
                $package = Package::where('user_id', $authUser->id)
                    ->where("id", $id)
                    ->first();
                $package->order = ++$i;
                $package->save();
            }
        }
        return response()->json(['data' => $idList], StatusCode::HTTP_OK);
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

        $packageCategoryId = $request->has('categoryId') ? $request['categoryId'] : null;
        $title = $request->has('title') ? $request['title'] : null;
        $description = $request->has('description') ? $request['description'] : null;
        $session = $request->has('session') ? $request['session'] : null;
        $timePerSession = $request->has('timePerSession') ? $request['timePerSession'] : null;
        $price = $request->has('price') ? $request['price'] : null;
        $discount = $request->has('discount') ? $request['discount'] : null;
        $attendeesMin = $request->has('attendeesMin') ? $request['attendeesMin'] : null;
        $attendeesMax = $request->has('attendeesMax') ? $request['attendeesMax'] : null;
        $isSpecialPrice = $request->has('isSpecialPrice') ? $request['isSpecialPrice'] : 0;

        $user = Auth::user();

        $package = new Package();
        $package->package_category_id = $packageCategoryId;
        $package->user_id = $user->id;
        $package->status = 0;
        if ($package->save()) {
            $packageDetail = new PackageDetail();
            $packageDetail->package_id = $package->id;
            $packageDetail->title = $title;
            $packageDetail->description = $description;
            $packageDetail->session = $session;
            $packageDetail->time_per_session = $timePerSession;
            $packageDetail->price = $price;
            $packageDetail->attendees_min = $attendeesMin;
            $packageDetail->attendees_max = $attendeesMax;
            $packageDetail->discount = $discount;
            $packageDetail->is_special_price = $isSpecialPrice;
            if ($packageDetail->save()) {

                // Progress Step
                $progressService = new ProgressService();
                $progress = $progressService->getUserPackagePageProgress($user);

                // Transformed package
                $transformerService = new TransformerService();
                $transformedPackage = $transformerService->getTransformedData(new Item($package, new PackageTransformer($user)));

                $response['package'] = $transformedPackage;
                $response['progress'] = $progress;
                $response['status'] = 'success';
                $response['message'] = 'Successfully created your package';
            }
        }

        return $response;
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
        $response = [];
        $user = Auth::user();
        $package = Package::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        if ($package) {
            $packageDetail = $package->details;
            $packageDetail->title = $request->has('title') ? $request['title'] : '';
            $packageDetail->description = $request->has('description') ? $request['description'] : '';
            $packageDetail->session = $request->has('session') ? $request['session'] : 0;
            $packageDetail->time_per_session = $request->has('timePerSession') ? $request['timePerSession'] : null;
            $packageDetail->price = $request->has('price') ? $request['price'] : 0;
            $packageDetail->is_special_price = $request->has('isSpecialPrice') ? $request['isSpecialPrice'] : null;
            $packageDetail->discount = $request->has('discount') ? $request['discount'] : 0;
            $packageDetail->transport_fee = $request->has('transportFee') ? $request['transportFee'] : 0;
            $packageDetail->attendees_min = $request->has('attendeesMin') ? $request['attendeesMin'] : 0;
            $packageDetail->attendees_max = $request->has('attendeesMax') ? $request['attendeesMax'] : 0;
            $packageDetail->completed_by_days = $request->has('completedByDays') ? $request['completedByDays'] : 0;
            if ($packageDetail->save()) {

                // Progress Step
                $stepService = new StepService();
                $stepService->manage($user, Constants::PAGE_KEY_PACKAGE);
                $progress = $stepService->stepInPercent($user, Constants::PAGE_KEY_PACKAGE);

                // Transformed package
                $transformerService = new TransformerService();
                $transformedPackages = $transformerService->getTransformedData(new Item($package, new PackagesTransformer($user)));

                $response['package'] = $transformedPackages;
                $response['progress'] = $progress;
                $response['status'] = 'success';
                $response['message'] = 'Successfully Update your package';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Something went wrong, try again.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something went wrong, try again.';
        }

        return $response;
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
        $package = Package::where('id', $id)->where('user_id', Auth::id())->first();
        if ($package) {
            $package->details->delete();
            $package->delete();
            $response['status'] = 'success';
            $response['message'] = 'Successfully removed';
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Sorry! Package not found';
        }

        return $response;
    }

    public function saveHourlyRate(Request $request)
    {
        $response = [];

        $validator = Validator::make($request->all(), [
            'hourly_rate' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['message'] = 'Your input data is not valid';
        }

        $user = Auth::user();
        $userPackageSetting = $user->ownPackageSetting ?? new PackageUserSetting();
        $userPackageSetting->user_id = $user->id;
        $userPackageSetting->hourly_rate = $request['hourly_rate'];


        if ($userPackageSetting->save()) {
            // Change user page Step
            $stepService = new StepService();
            $stepService->manage($user, Constants::PAGE_KEY_PACKAGE);
            $progress = $stepService->stepInPercent($user, Constants::PAGE_KEY_PACKAGE);

            // Packages
            $response['packages'] = $this->getUserPackages($user);

            $response['status'] = 'success';
            $response['hourly_rate'] = $userPackageSetting->hourly_rate;
            $response['message'] = 'Successfully saved your hourly rate';
            $response['progress'] = $progress;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something went wrong, try again';
        }

        return $response;
    }

    public function changeStatus(Request $request)
    {
        $response = [];
        $package = Package::where('user_id', Auth::id())
            ->where('id', $request['id'])
            ->first();
        if ($package) {
            $package->status = $request['changed_status'];
            if ($package->save()) {
                $response['status'] = 'success';
                $response['message'] = 'Successfully changed your package status';
            }
        } else {
            $response['status'] = 'error';
            $request['message'] = 'Sorry, not found';
        }
        return $response;
    }

    public function toggleQuickBooking(Request $request)
    {
        try {
            $user = Auth::user();
            $packageUserSetting = $user->ownPackageSetting;
            if (!$packageUserSetting) {
                throw new \Exception('User package setting not found.');
            }
            $changeValue = !$packageUserSetting->is_quick_booking;
            $packageUserSetting->is_quick_booking = $changeValue;
            $packageUserSetting->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully changed quick booking status',
                'changedValue' => $changeValue
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
