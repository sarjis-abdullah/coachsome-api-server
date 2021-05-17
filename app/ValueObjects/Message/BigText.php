<?php


namespace App\ValueObjects\Message;


class BigText
{
    private $fields = [
        'key'=> 'big_text',
        'text'=> '',
        'status'=> 'Initial',
    ];


    public function __construct(array $fields = [])
    {

        if(isset($fields['text'])){
            $this->fields['text'] = $fields['text'];
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
