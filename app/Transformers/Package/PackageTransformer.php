<?php


namespace App\Transformers\Package;


use App\Entities\Package;
use App\Entities\User;
use App\Services\PackageService;
use League\Fractal;

class PackageTransformer extends Fractal\TransformerAbstract
{

    private $packageService, $user;

    public function __construct(User $user)
    {
        $this->packageService = new PackageService();
        $this->user = $user;
    }

    public function transform(Package $item)
    {
        $discount = $item->details->discount ?? 0.00;
        $originalPrice = $this->packageService->calculateOriginalPrice($this->user, $item);
        $salePrice = $this->packageService->calculatePackageSalePrice($originalPrice, $discount);
        return [
            'id' => $item->id ?? '',
            'title' => $item->details->title ?? '',
            'description' => $item->details->description ?? '',
            'category' => $item->category,
            'status' => $item->status ?? '',
            'attendeesMin' => $item->details->attendees_min ?? '',
            'attendeesMax' => $item->details->attendees_max ?? '',
            'completedByDays' => $item->details->completed_by_days ?? 0,
            'isSpecialPrice' => $item->details->is_special_price ?? '',
            'transportFee' => $item->details->transport_fee ?? 0.00,
            'originalPrice' => $originalPrice,
            'salePrice' => $salePrice,
            'session' => $item->details->session ?? 0,
            'discount' => $item->details->discount ?? 0.00,
            'timePerSession' => $item->details->time_per_session ?? ''
        ];

    }
}
