<?php

namespace App\Services\GiftCard;

use App\Data\TransactionType;
use App\Entities\GiftTransaction;

class GiftCardService
{
    public function balance($user)
    {
        $giftCardBalance = 0.00;
        GiftTransaction::where('user_id', $user->id)
            ->get()
            ->each(function ($item) use (&$giftCardBalance) {
                if ($item->type == TransactionType::DEBIT) {
                    $giftCardBalance += $item->amount;
                } else {
                    $giftCardBalance -= $item->amount;
                }
            });
        return $giftCardBalance;
    }
}
