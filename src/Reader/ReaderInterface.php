<?php

namespace JsonToDatabase\Reader;

interface ReaderInterface
{
    public function startAt(int $index):void;

    /**
     * @return mixed
     */
    public function read();
}
