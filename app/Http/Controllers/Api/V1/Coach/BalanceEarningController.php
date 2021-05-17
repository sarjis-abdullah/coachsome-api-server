<?php

namespace App\Http\Controllers\Api\V1\Coach;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\BalanceEarning;
use App\Entities\Booking;
use App\Entities\PayoutRequest;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\BalanceEarningService;
use App\Services\CurrencyService;
use BenMajor\ExchangeRatesAPI\ExchangeRatesAPI;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BalanceEarningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $userCurrency = $request->header('Currency-Code');

            $timeLine = [];
            $dashboardInfo = [
                'newCustomers' => 0,
                'totalIncome' => 0.00,
                'averageRevenuePerCustomer' => 0,
                'totalPackageSold' => 0,
                'totalSessionCompleted' => 0,
                'currency' => $userCurrency,
            ];

            $authUser = Auth::user();
            $balanceEarningService = new BalanceEarningService();
            $currencyService = new CurrencyService();


            if (!$startDate && !$endDate) {
                $currentDate = Carbon::now();
                $startDate = $currentDate->copy()->subMonths(5)->format('Y-m-d');
                $endDate = $currentDate->format('Y-m-d');
            } else {
                if ($startDate >= $endDate) {
                    throw new \Exception('Start date should not greater or equal than end date.');
                }
            }

            $results = $balanceEarningService->getUserBalanceEarningInfo($authUser, $userCurrency);
            $overviews = collect($results['overviews'])->filter(function ($item) use ($startDate, $endDate) {
                if (date('Y-m-d', strtotime($item->date)) >= date('Y-m-d', strtotime($startDate)) &&
                    date('Y-m-d', strtotime($item->date)) <= date('Y-m-d', strtotime($endDate))) {
                    return true;
                } else {
                    return false;
                }
            })->values()->toArray();


            $periods = CarbonPeriod::create($startDate, '1 month', $endDate);

            foreach ($periods as $period) {
                $currentPeriodTotalIncome = 0.00;
                $formattedFirstDayOfMonth = date('Y-m-d H:i:s', strtotime($period->firstOfMonth()->format('Y-m-d')));
                $formattedLastDayOfMonth = date('Y-m-d H:i:s', strtotime($period->lastOfMonth()->format('Y-m-d')));

                $acceptedBookings = Booking::with(['order'])
                    ->where('package_owner_user_id', $authUser->id)
                    ->where('status', 'Accepted')
                    ->where(function ($q) use ($formattedFirstDayOfMonth, $formattedLastDayOfMonth) {
                        $q->where('booking_date', '>=', $formattedFirstDayOfMonth);
                        $q->where('booking_date', '<=', $formattedLastDayOfMonth);
                    })->get();

                foreach ($acceptedBookings as $item) {
                    $rate = 0.00;
                    $dashboardInfo['totalPackageSold']++;
                    $dashboardInfo['totalSessionCompleted'] += $item->bookingTimes->where('status', 'Accepted')->count();
                    $order = $item->order;
                    $bookingSetting = json_decode($item->booking_settings_snapshot);

                    if ($bookingSetting) {
                        $rate = $bookingSetting->package_owner_gnr_service_fee / 100;
                    }

                    if ($order) {
                        $convertedAmount = $currencyService->convert(
                            $order->package_sale_price,
                            $order->currency,
                            $userCurrency
                        );
                        $fee = round(($convertedAmount * $rate), 2);
                        $income = round(($convertedAmount - $fee), 2);
                        $dashboardInfo['totalIncome'] = round($dashboardInfo['totalIncome'] + $income, 2);
                        $currentPeriodTotalIncome += $income;
                    }
                };

                $dashboardInfo['newCustomers'] += $acceptedBookings->count();
                $periodElement = new \stdClass();
                $periodElement->month = $period->firstOfMonth()->format('M');
                $periodElement->firstDay = $period->firstOfMonth()->format('Y-m-d');
                $periodElement->lastDay = $period->firstOfMonth()->format('Y-m-d');
                $periodElement->customer = $acceptedBookings->count();
                $periodElement->income = round($currentPeriodTotalIncome, 2);
                $timeLine[] = $periodElement;
            }


            $totalCustomers = Booking::select('package_buyer_user_id')
                ->where('status', 'Accepted')
                ->where('package_owner_user_id', $authUser->id)
                ->where(function ($q) use ($startDate, $endDate) {
                    if ($startDate && $endDate) {
                        $q->where('booking_date', '>=', date('Y-m-d H:i:s', strtotime($startDate)));
                        $q->where('booking_date', '<=', date('Y-m-d H:i:s', strtotime($endDate)));
                    }
                })->get()->count();


            $dashboardInfo['startDate'] = $startDate;
            $dashboardInfo['endDate'] = $endDate;
            $dashboardInfo['averageRevenuePerCustomer'] = round($dashboardInfo['totalIncome'] / $totalCustomers, 2);

            return response()->json([
                'balanceEarnings' => $overviews,
                'currentBalance' => $results['currentBalance'],
                'payoutRequest' => $results['payoutRequest'],
                'dashboardInfo' => $dashboardInfo,
                'timeLine' => $timeLine
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
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
