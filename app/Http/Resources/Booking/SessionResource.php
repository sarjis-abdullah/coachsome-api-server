<?php

namespace App\Http\Resources\Booking;

use App\Data\ExerciseData;
use App\Entities\ExerciseAsset;
use App\Entities\ExerciseSportCategory;
use App\Entities\SportCategory;
use App\Services\Media\MediaService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $id = $this->id;
        $booking_id = $this->booking_id;
        $created_at = $this->created_at;
        $requester_user = $this->requesterUser->first_name." ".$this->requesterUser->last_name;
        $requester_to_user = $this->requesterToUser->first_name." ".$this->requesterToUser->last_name;
        $requested_date = $this->requested_date;
        $created_at = $this->created_at;
        $start_time = $this->start_time;
        $end_time = $this->end_time;
        $status = $this->status;

        return [
            'id' => $id,
            'booking_id' => $booking_id,
            'created_at' => $created_at,
            'requested_by' => $requester_user,
            'requested_to' => $requester_to_user,
            'requested_date' => date('d-m-Y g:i a', strtotime($requested_date)),
            'start_time' => $start_time,
            'end_time' => $end_time,
            'status' => $status,
            'created_at' => date('d-m-Y g:i a', strtotime($created_at)),
        ];
    }
}
