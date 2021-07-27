<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entities\Package;
use App\Entities\PendingBooking;
use App\Entities\User;
use App\Helpers\StringUtility;
use App\Http\Controllers\Controller;
use App\Mail\PendingBookingRequestConfirmation;
use App\Services\TransformerService;
use App\Services\TranslationService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;

class PendingBookingController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];

        try {

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'mobileNumber' => 'required',
                'coachDescription' => 'nullable|string',
                'userName' => 'required',
                'packageId' => 'nullable',
            ]);

            // Payloads
            [
                'name' => $name,
                'email' => $email,
                'mobileNumber' => $mobileNumber,
                'packageId' => $packageId,
                'userName' => $userName,
                'coachDescription' => $coachDescription
            ] = $request->all();

            $languageCode = $request->header('Language-Code');

            $userService = new UserService();
            $translationService = new TranslationService();

            $package = Package::find($packageId);
            $packageOwnerUser = User::where('user_name', $userName)->first();
            $firstName = StringUtility::firstWord($name);
            $lastName = StringUtility::lastWord($name);
            $generatedUserName = $userService->generateUserName($firstName, $lastName);

            if (!$packageOwnerUser) {
                throw new \Exception('Package owner does not exist.');
            }

            $mUser = User::where('email', $email)->first();
            if (!$mUser) {
                $mUser = new User();
                $mUser->first_name = $firstName;
                $mUser->last_name = $lastName;
                $mUser->full_name = $name;
                $mUser->email = $email;
                $mUser->user_name = $generatedUserName;
                $mUser->save();
            }

            $translation = $translationService->getKeyByLanguageCode($languageCode);
            $token = Uuid::uuid1()->toString();
            $link = env('APP_CLIENT_DOMAIN')
                . env('APP_CLIENT_DOMAIN_FRONTEND_PREFIX')
                . 'pendingBookingRequestConfirmation?token='
                . $token;

            $mPendingBooking = new PendingBooking();
            $mPendingBooking->package_id = $packageId;
            $mPendingBooking->package_details = $package ? $package->details->toJson() : null;
            $mPendingBooking->customer_user_id = $mUser->id;
            $mPendingBooking->package_owner_user_id = $packageOwnerUser->id;
            $mPendingBooking->booking_date = Carbon::now();
            $mPendingBooking->customer_text = $coachDescription;
            $mPendingBooking->customer_mobile_no = $mobileNumber;
            $mPendingBooking->confirmation_token = $token;
            $mPendingBooking->booking_status = 'Pending';
            $mPendingBooking->save();

            Mail::to($mUser)->send(new PendingBookingRequestConfirmation(
                $name,
                $link,
                $translation
            ));


            $response['status'] = 'success';
            $response['message'] = 'Successfully updated.';

            return response()->json($response, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                $response['status'] = 'error';
                $response['message'] = $e->validator->errors()->first();
                return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }

            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function confirm(Request $request)
    {
       $token = $request->confirmation_token;

        try {
            if (!$token) {
                throw new \Exception('Sorry confirmation token is missing.');
            }

            $pendingBooking = PendingBooking::where('confirmation_token', $token)->first();

            if (!$pendingBooking) {
                throw new \Exception('Sorry token is not matching.');
            }

            $pendingBooking->confirm_mail_date = Carbon::now();
            $pendingBooking->activation_mail_date = Carbon::now();
            $pendingBooking->booking_status = 'Confirmed';
            $pendingBooking->save();

            return response()->json([
                'message'=>'Successfully confirmed'
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                StatusCode::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }


}
