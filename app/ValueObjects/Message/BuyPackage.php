<?php


namespace App\ValueObjects\Message;


class BuyPackage
{
    private $fields = [
        'key'=> 'buy_package',
        'orderSnapshot'=> null,
        'packageSnapshot'=> null,
        'packageBuyerName'=> '',
        'status'=> 'Initial',
    ];


    public function __construct(array $fields = [])
    {

        if(isset($fields['orderSnapshot'])){
            $this->fields['orderSnapshot'] = $fields['orderSnapshot'];
        }

        if(isset($fields['packageSnapshot'])){
            $this->fields['packageSnapshot'] = $fields['packageSnapshot'];
        }

        if(isset($fields['packageBuyerName'])){
            $this->fields['packageBuyerName'] = $fields['packageBuyerName'];
        }


        if(isset($fields['status'])){
            $this->fields['status'] = $fields['status'];
        }
    }

    public function toJson()
    {
        return json_encode($this->fields);
    }

}
