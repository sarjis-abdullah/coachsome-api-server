<?php

namespace App\Http\Controllers\Api\V1\Admin\User;

use App\Data\Constants;
use App\Entities\UserLog;
use App\Http\Controllers\Controller;
use App\Services\TransformerService;
use App\Transformers\Admin\User\UserLogListTransformer;
use Illuminate\Http\Request;
use League\Fractal\Resource\Collection;

class UserLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = [];
        $userLogs = UserLog::orderBy('created_at','DESC')->get();
        $transformerService = new TransformerService();
        $transformedUserLogList = $transformerService->getTransformedData(new Collection($userLogs, new UserLogListTransformer()));
        $response['userLogs'] = $transformedUserLogList;
        return response()->json($response, Constants::HTTP_OK);
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
