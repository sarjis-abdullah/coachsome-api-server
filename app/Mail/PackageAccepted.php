<?php

namespace App\Mail;

use App\Services\CurrencyService;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PackageAccepted extends Mailable
{
    use Queueable, SerializesModels;

    private $booking = null;
    private $translationService;
    private $currencyService;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
        $this->translationService = new TranslationService();
        $this->currencyService = new CurrencyService();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $chatNowUrl = null;
        $hashTagUrl = env('APP_SERVER_DOMAIN');
        $paymentMethod ='';
        $stairCaseUrl = env('APP_SERVER_DOMAIN').'/public/assets/images/border-staircase.svg';
        $packageSellerProfileImage = env('APP_SERVER_DOMAIN').'/public/assets/images/profile-default.jpg';
        $invoiceNumber = '';
        $packagePrice = 0;
        $packageTotalPrice = 0;
        $serviceFee = 0.00;
        $vat = 0.00;
        $languageCode = 'en';
        $translations = null;
        $packageName = '';

        $packageSellerUser = $this->booking->packageOwnerUser;
        $packageBuyerUser = $this->booking->packageBuyerUser;
        $order = $this->booking->order;
        $payment = $order ? $order->payment : null;
        $packageSnapshot = $order ? json_decode($order->package_snapshot) : null;

        if($languageCode  == 'da'){
            $translations = $this->translationService->getKeyByLanguageCode($languageCode);
        } else {
            $translations = $this->translationService->getKeyByLanguageCode($languageCode);
        }

        $packageSellerProfile = $packageSellerUser->profile;

        if($packageSnapshot){
            $packageName = $packageSnapshot->details ? $packageSnapshot->details->title: '';
        }

        if($packageSellerProfile){
            $image = $packageSellerProfile->getImage();
            if($image){
                $packageSellerProfileImage = env('APP_SERVER_DOMAIN').'/public/storage/images/'.$image;
            }
        }

        if($order){
            $invoiceNumber = $order->key;
            $packagePrice = $this->currencyService->format(
                $order->number_of_attendees* $order->package_sale_price,
                $order->currency
            );
            $packageTotalPrice = $this->currencyService->format( $order->total_amount,   $order->currency);
            $serviceFee = $this->currencyService->format($order->service_fee,$order->currency);
        }

        if($payment){
            $paymentMethod = $payment->method;
        }

        $chatNowUrl = env('APP_CLIENT_DOMAIN').'/pages/chat?userId='.$packageSellerUser->id;
        $termsUrl = env('APP_CLIENT_DOMAIN').'/pages/terms-of-use';
        $conditionUrl = env('APP_CLIENT_DOMAIN').'/pages/privacy-policy';

        $emailData = [
            'packageName'=>$packageName,
            'packageBuyerFirstName'=> $packageBuyerUser->first_name,
            'packageSellerFullName'=> $packageSellerUser->first_name. ' '. $packageSellerUser->last_name,
            'packageSellerProfileImage'=>$packageSellerProfileImage,
            'chatNowUrl'=>$chatNowUrl,
            'stairCaseUrl'=> $stairCaseUrl,
            'invoiceNumber'=> $invoiceNumber,
            'paymentMethod'=>$paymentMethod,
            'packagePrice'=>$packagePrice,
            'serviceFee'=>$serviceFee,
            'totalPrice'=> $packageTotalPrice,
            'vat'=>$vat,
            'hashTagUrl'=> $hashTagUrl,
            'termsUrl'=>$termsUrl,
            'conditionUrl'=> $conditionUrl,
            'translations' => $translations
        ];
        return $this->view('emails.packageAccepted')
            ->with($emailData);
    }
}
