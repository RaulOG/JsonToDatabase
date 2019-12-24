<?php

namespace JsonToDatabase\Reader\Adapter;

use JsonToDatabase\Reader\Exception\ReaderException;
use JsonToDatabase\Reader\ReaderInterface;
use pcrov\JsonReader\InputStream\IOException;
use pcrov\JsonReader\JsonReader;
use pcrov\JsonReader\Parser\ParseException;

class JsonAdapter implements ReaderInterface
{
    private $reader;

    public function __construct(JsonReader $reader, $filePath)
    {
        $this->reader = $reader;

        try {
            $this->reader->open($filePath);
            $this->reader->read();
            $this->reader->read();
        } catch (IOException $e) {
            throw new ReaderException("File not found");
        } catch (ParseException $e) {
            throw new ReaderException("File is empty");
        }
    }
    
    public function startAt(int $index):void
    {
        for($i = 0; $i < $index; $i++) {
            $this->reader->next();
        }
    }

    public function read()
    {
        if($this->isEndOfFile()){
            return null;
        }
        $value = $this->reader->value();
        $this->reader->next();
        return $value;
    }

    public function __destruct()
    {
        $this->reader->close();
    }

    private function isEndOfFile()
    {
        return is_null($this->reader->value());
    }
}
