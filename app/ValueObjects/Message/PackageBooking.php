<?php


namespace App\ValueObjects\Message;


class PackageBooking
{
    private $fields = [
        'key' => 'booking_package',
        'packageTitle' => null,
        'orderKey' => null,
        'buyerName' => null,
        'amount' => null,
        'currencyCode' => null,
        'session' => null,
        'bookingId' => null,
        'buyerText' => null,
        'packageSnapshot'=> null,
        'status' => null,
    ];


    public function __construct(array $fields = [])
    {

        if (isset($fields['orderKey'])) {
            $this->fields['orderKey'] = $fields['orderKey'];
        }

        if (isset($fields['buyerName'])) {
            $this->fields['buyerName'] = $fields['buyerName'];
        }

        if (isset($fields['amount'])) {
            $this->fields['amount'] = $fields['amount'];
        }

        if (isset($fields['currencyCode'])) {
            $this->fields['currencyCode'] = $fields['currencyCode'];
        }

        if (isset($fields['session'])) {
            $this->fields['session'] = $fields['session'];
        }

        if (isset($fields['packageTitle'])) {
            $this->fields['packageTitle'] = $fields['packageTitle'];
        }

        if (isset($fields['bookingId'])) {
            $this->fields['bookingId'] = $fields['bookingId'];
        }

        if (isset($fields['buyerText'])) {
            $this->fields['buyerText'] = $fields['buyerText'];
        }

        if (isset($fields['packageSnapshot'])) {
            $this->fields['packageSnapshot'] = $fields['packageSnapshot'];
        }

        if (isset($fields['status'])) {
            $this->fields['status'] = $fields['status'];
        }
    }

    public function toJson()
    {
        return json_encode($this->fields);
    }
}
