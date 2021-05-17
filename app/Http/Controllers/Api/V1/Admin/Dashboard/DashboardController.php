<?php


namespace App\Http\Controllers\Api\V1\Admin\Dashboard;


use App\Data\RoleData;
use App\Data\StatusCode;
use App\Entities\Booking;
use App\Entities\BookingTime;
use App\Entities\Currency;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\BalanceEarningService;
use App\Services\CurrencyService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {

            $data = [
                'users' => 0,
                'packageSold' => 0.00,
                'sessionCompleted' => 0.00,
                'avgPricePerPackage' => 0.00,
                'totalSales' => 0.00,
                'totalProfit' => 0.00,
                'currency' => '',
                'chart' => [
                    'users' => [
                        'xaxis' => [],
                        'yaxis' => [],
                    ],
                    'packageSold' => [
                        'xaxis' => [],
                        'yaxis' => [],
                    ],
                    'sessionCompleted' => [
                        'xaxis' => [],
                        'yaxis' => [],
                    ],
                    'avgPricePerPackage' => [
                        'xaxis' => [],
                        'yaxis' => [],
                    ],
                    'totalSale' => [
                        'xaxis' => [],
                        'yaxis' => [],
                    ],
                    'totalProfit' => [
                        'xaxis' => [],
                        'yaxis' => [],
                    ],
                ]
            ];

            $validator = Validator::make($request->all(), [
                'startDate' => "required|date",
                'endDate' => "required|date",
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->getMessageBag()->first());
            }

            $startDate = date('Y-m-d H:i:s', strtotime($request->startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($request->endDate));

            if ($startDate >= $endDate) {
                throw new \Exception('Start date should not greater or equal than end date.');
            }


            $authUser = User::whereRoleIs(RoleData::ROLE_KEY_COACH)->get();
            $balanceEarningService = new BalanceEarningService();
            $currencyService = new CurrencyService();

            $toCurrency = $currencyService->getDefaultBasedCurrency();
            $data['currency'] = $toCurrency->code;

            // Users
            $data['users'] = User::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->get()
                ->count();

            // Package Sold
            $data['packageSold'] = Booking::with(['order'])
                ->where('status', 'Accepted')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('booking_date', '>=', $startDate);
                    $q->where('booking_date', '<=', $endDate);
                })->get()->count();

            // Session completed
            $data['sessionCompleted'] = BookingTime::where('requested_date', '>=', $startDate)
                ->where('requested_date', '<=', $endDate)
                ->where('status', 'Accepted')
                ->count();

            // Avg price per package and total sales and total profit
            $totalSales = 0.00;
            $totalProfit = 0.00;
            $bookingsForAvgPerPackage = Booking::with(['order'])
                ->where('status', 'Accepted')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('booking_date', '>=', $startDate);
                    $q->where('booking_date', '<=', $endDate);
                })->get();
            foreach ($bookingsForAvgPerPackage as $booking) {
                $order = $booking->order;
                if ($order) {
                    $totalSales += $currencyService->convert($order->total_amount, $order->currency, $toCurrency->code);
                    $totalProfit += $currencyService->convert(
                        ($order->total_amount - $order->service_fee) * $booking->package_owner_service_fee_snapshot / 100, $order->currency, $toCurrency->code
                    );
                }
            }
            $data['avgPricePerPackage'] = round($totalSales / $data['packageSold'], 2);
            $data['totalSales'] = $totalSales;
            $data['totalProfit'] = $totalProfit;

            // Monthly calculation
            $periods = CarbonPeriod::create($startDate, '1 month', $endDate);
            foreach ($periods as $period) {

                $commonMonth = $period->firstOfMonth()->format('M y');
                $formattedFirstDayOfMonth = date('Y-m-d H:i:s', strtotime($period->firstOfMonth()->format('Y-m-d')));
                $formattedLastDayOfMonth = date('Y-m-d H:i:s', strtotime($period->lastOfMonth()->format('Y-m-d')));

                // Users
                $data['chart']['users']['xaxis'][] = $commonMonth;
                $data['chart']['users']['yaxis'][] = User::where('created_at', '>=', $formattedFirstDayOfMonth)
                    ->where('created_at', '<=', $formattedLastDayOfMonth)
                    ->get()
                    ->count();

                // Package Sold
                $data['chart']['packageSold']['xaxis'][] = $commonMonth;
                $data['chart']['packageSold']['yaxis'][] = Booking::with(['order'])
                    ->where('status', 'Accepted')
                    ->where(function ($q) use ($formattedFirstDayOfMonth, $formattedLastDayOfMonth) {
                        $q->where('booking_date', '>=', $formattedFirstDayOfMonth);
                        $q->where('booking_date', '<=', $formattedLastDayOfMonth);
                    })->get()->count();

                // Sessions Completed
                $data['chart']['sessionCompleted']['xaxis'][] = $commonMonth;
                $data['chart']['sessionCompleted']['yaxis'][] = BookingTime::where('requested_date', '>=', $formattedFirstDayOfMonth)
                    ->where('requested_date', '<=', $formattedLastDayOfMonth)
                    ->where('status', 'Accepted')
                    ->count();

                // Avg price per package and total sales and total profit
                $totalSales = 0.00;
                $totalProfit = 0.00;
                $bookingsForAvgPerPackage = Booking::with(['order'])
                    ->where('status', 'Accepted')
                    ->where(function ($q) use ($formattedFirstDayOfMonth, $formattedLastDayOfMonth) {
                        $q->where('booking_date', '>=', $formattedFirstDayOfMonth);
                        $q->where('booking_date', '<=', $formattedLastDayOfMonth);
                    })->get();
                foreach ($bookingsForAvgPerPackage as $booking) {
                    $order = $booking->order;
                    if ($order) {
                        $totalSales += $currencyService->convert($order->total_amount, $order->currency, $toCurrency->code);
                        $totalProfit += $currencyService->convert(
                            ($order->total_amount - $order->service_fee) * $booking->package_owner_service_fee_snapshot / 100, $order->currency, $toCurrency->code
                        );
                    }
                }
                $data['chart']['avgPricePerPackage']['xaxis'][] = $commonMonth;
                $data['chart']['avgPricePerPackage']['yaxis'][] = round($totalSales / $data['packageSold'], 2);
                $data['chart']['totalSale']['xaxis'][] = $commonMonth;
                $data['chart']['totalSale']['yaxis'][] = $totalSales;
                $data['chart']['totalProfit']['xaxis'][] = $commonMonth;
                $data['chart']['totalProfit']['yaxis'][] = $totalProfit;

            }


            return response()->json([
                'data' => $data
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
