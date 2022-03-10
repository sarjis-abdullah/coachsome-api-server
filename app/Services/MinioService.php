<?php

namespace App\Services;

class MinioService
{

    public  function __construct()
    {
        //
    }

    public function getAttachmentUrl($url){
        
        return env('MINIO_ENDPOINT').'/'.env('MINIO_BUCKET').'/'.$url;
    }
}