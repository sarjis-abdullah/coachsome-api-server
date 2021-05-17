<?php


namespace App\Services;


use App\Serializer\CustomSerializer;
use League\Fractal\Manager;

class TransformerService
{
    private $manager = null;

    public function __construct()
    {
        $this->manager = (new Manager())->setSerializer(new CustomSerializer());
    }

    private function getManager()
    {
        return $this->manager;
    }

    public function getTransformedData($resource)
    {
        return $this->getManager()->createData($resource);
    }

    public function setParseInclude($includes = [])
    {
        $this->getManager()->parseIncludes($includes);
        return $this;
    }

}
