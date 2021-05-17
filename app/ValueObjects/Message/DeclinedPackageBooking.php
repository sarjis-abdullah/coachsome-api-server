<?php


namespace App\ValueObjects\Message;


class DeclinedPackageBooking
{
    private $fields = [
        'key' => 'declined_package_booking',
        'orderSnapshot' => null,
        'packageSnapshot' => null,
        'status' => 'Initial',
    ];


    public function __construct(array $fields = [])
    {

        if (isset($fields['orderSnapshot'])) {
            $this->fields['orderSnapshot'] = $fields['orderSnapshot'];
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
