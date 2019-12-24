<?php

namespace JsonToDatabase\Reader\Factory;

use JsonToDatabase\Reader\Adapter\JsonAdapter;
use JsonToDatabase\Reader\ReaderInterface;
use pcrov\JsonReader\JsonReader;

class ReaderFactory
{
    /**
     * Location where readers will attempt to find their files
     * @var string
     */
    private $baseLocation;

    public function __construct(string $baseLocation)
    {
        $this->baseLocation = $baseLocation;
    }

    public function makeFor(string $fileName): ReaderInterface
    {
        $filePath = "$this->baseLocation/$fileName";
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($extension == "json") {
            return new JsonAdapter(new JsonReader(), $filePath);
        }

        throw new \Exception("Not supported");
    }
}
