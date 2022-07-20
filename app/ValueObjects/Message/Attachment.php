<?php


namespace App\ValueObjects\Message;


class Attachment
{
    private $fields = [
        'key'=> '',
        'url'=> '',
        'label'=> '',
        'extension' => ''
    ];


    public function __construct(array $fields = [])
    {

        if(isset($fields['url'])){
            $this->fields['url'] = $fields['url'];
        }
        if(isset($fields['key'])){
            $this->fields['key'] = $fields['key'];
        }
        if(isset($fields['label'])){
            $this->fields['label'] = $fields['label'];
        }
        if(isset($fields['extension'])){
            $this->fields['extension'] = $fields['extension'];
        }
    }

    public function toJson()
    {
        return json_encode($this->fields);
    }
}
