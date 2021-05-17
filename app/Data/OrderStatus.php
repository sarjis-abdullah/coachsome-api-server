<?php


namespace App\Data;


class OrderStatus
{
    const INITIAL = "Initial";
    const CAPTURE = "Capture";
    const AUTHORIZED = "Authorized";
    const CANCELED = "Canceled";
    const REJECTED = "Rejected";
}
