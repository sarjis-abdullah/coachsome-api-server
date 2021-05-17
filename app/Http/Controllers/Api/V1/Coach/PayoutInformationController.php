<?php

namespace App\Http\Controllers\Api\V1\Coach;

use App\Data\StatusCode;
use App\Entities\PayoutInformation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PayoutInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $authUser = Auth::user();
            if($authUser->payoutInformation){
                $payoutInformation = $authUser->payoutInformation;
            } else {
                $payoutInformation = new PayoutInformation();
                $payoutInformation->user_id = $authUser->id;
                $payoutInformation->save();
            }
            return response()->json([
                'data' => [
                    "isPersonal" => $payoutInformation->is_personal,
                    "isCompany" => $payoutInformation->is_company,
                    "companyName" => $payoutInformation->company_name,
                    "vatNumber" => $payoutInformation->vat_number,
                    "isVatRegistered" => $payoutInformation->is_vat_registered,
                    "accHolderName" => $payoutInformation->acc_holder_name,
                    "cca2" => $payoutInformation->cca2,
                    "address" => $payoutInformation->address,
                    "zipCode" => $payoutInformation->zip_code,
                    "city" => $payoutInformation->city,
                    "bankName" => $payoutInformation->name_of_bank,
                    "registration" => $payoutInformation->registration,
                    "account" => $payoutInformation->account,
                ],
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [$e->getMessage()]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);

        }
    }

    /**
     * Save the info
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $authUser = Auth::user();
            $payoutInformation = $authUser->payoutInformation ?? new PayoutInformation();
            $payoutInformation->user_id = $authUser->id;
            $payoutInformation->is_personal = $request->isPersonal;
            $payoutInformation->is_company = $request->isCompany;
            $payoutInformation->vat_number = $request->vatNumber;
            $payoutInformation->company_name = $request->companyName;
            $payoutInformation->is_vat_registered = $request->isVatRegistered;
            $payoutInformation->cca2 = $request->cca2;
            $payoutInformation->address = $request->address;
            $payoutInformation->zip_code = $request->zipCode;
            $payoutInformation->city = $request->city;
            $payoutInformation->acc_holder_name = $request->accHolderName;
            $payoutInformation->name_of_bank = $request->bankName;
            $payoutInformation->registration = $request->registration;
            $payoutInformation->account = $request->account;
            $payoutInformation->save();


            return response()->json([
                'data' => [

                ],
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [$e->getMessage()]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
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
