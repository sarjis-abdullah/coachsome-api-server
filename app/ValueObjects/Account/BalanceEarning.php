<?php


namespace App\ValueObjects\Account;


class BalanceEarning
{
    public $id = 0;
    public $date = '';
    public $description = '';
    public $customerName = '';
    public $amount = 0;
    public $currency = 'DKK';
    public $fee = 0;
    public $income = 0;
    public $savings = 0.00;
    public $savingsToBalanceTransferredAmount = 0.00;
    public $balance = 0.00;
    public $balanceToPaidTransferredAmount = 0.00;
    public $paid = 0.00;
}
