<?php


namespace App\ValueObjects\Message;


class BigTextTimeBooking
{
    private $fields = [
        'key' => 'big_text_time_booking',
        'bookingTimeSnapshot' => null,
        'status' => 'Initial',
    ];


    public function __construct(array $fields = [])
    {

        if (isset($fields['bookingTimeSnapshot'])) {
            $this->fields['bookingTimeSnapshot'] = $fields['bookingTimeSnapshot'];
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
