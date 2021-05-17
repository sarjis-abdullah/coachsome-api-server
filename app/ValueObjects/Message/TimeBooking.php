<?php


namespace App\ValueObjects\Message;


class TimeBooking
{

    private $fields = [
        'key' => 'booking_time',
        'bookingTimeId' => null,
        'date' => null,
        'timeSlot' => null,
        'startTime' => null,
        'endTime' => null,
        'bookingLocation' => null,
        'packageSnapshot' => null,
        'status' => null,
    ];


    public function __construct(array $fields = [])
    {

        if (isset($fields['bookingTimeId'])) {
            $this->fields['bookingTimeId'] = $fields['bookingTimeId'];
        }

        if (isset($fields['date'])) {
            $this->fields['date'] = $fields['date'];
        }

        if (isset($fields['timeSlot'])) {
            $this->fields['timeSlot'] = $fields['timeSlot'];
        }

        if (isset($fields['startTime'])) {
            $this->fields['startTime'] = $fields['startTime'];
        }

        if (isset($fields['endTime'])) {
            $this->fields['endTime'] = $fields['endTime'];
        }

        if (isset($fields['bookingLocation'])) {
            $this->fields['bookingLocation'] = $fields['bookingLocation'];
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
