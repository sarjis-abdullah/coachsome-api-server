<?php


namespace App\ValueObjects\Message;


class Attachment
{
    private $fields = [
        'key'=> 'attachment',
        'url'=> '',
    ];


    public function __construct(array $fields = [])
    {

        if(isset($fields['url'])){
            $this->fields['url'] = $fields['url'];
        }
    }

    public function toJson()
    {
        return json_encode($this->fields);
    }
}
