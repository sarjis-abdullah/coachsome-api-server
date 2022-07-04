<?php


namespace App\ValueObjects\Message;


class Attachment
{
    private $fields = [
        'key'=> '',
        'url'=> '',
    ];


    public function __construct(array $fields = [])
    {

        if(isset($fields['url'])){
            $this->fields['url'] = $fields['url'];
        }
        if(isset($fields['key'])){
            $this->fields['key'] = $fields['key'];
        }
    }

    public function toJson()
    {
        return json_encode($this->fields);
    }
}
