<?php


namespace App\Http\Controllers\Api\V1\Coach;


use App\Data\Constants;
use App\Data\StatusCode;
use App\Services\MarketplaceService;
use App\Services\ProgressService;
use Illuminate\Support\Facades\Auth;

class ProgressController
{
   public function index()
   {
       $responseData = [];
       $progress = [];

       $user = Auth::user();
       $progressService= new ProgressService();
       $marketPlaceService = new MarketplaceService();

       $progress['profile'] = $progressService->getUserProfilePageProgress($user);
       $progress['package'] = $progressService->getUserPackagePageProgress($user);
       $progress['imageAndVideo'] = $progressService->getUserImageAndVideoPageProgress($user);
       $progress['geography'] = $progressService->getUserGeographyPageProgress($user);
       $progress['availability'] = $progressService->getUserAvailabilityPageProgress($user);
       $progress['review'] = $progressService->getUserReviewPageProgress($user);

       $responseData['isActive'] = $marketPlaceService->isActiveInMarketplace($user);
       $responseData['progress'] = $progress;

       return response()->json($responseData, StatusCode::HTTP_OK);
   }
}
