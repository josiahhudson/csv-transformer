<?php

namespace CsvTransform\OutputWriter;

use CsvTransform\OutputWriter;

class JsonWriter implements OutputWriter
{
    public function __construct(
        protected $fileHandle
    ) {}

    public function write(array $record): int|bool
    {
        // TODO: Exception on JSON encode issues?
        return fputs($this->fileHandle, json_encode($record) . "\n");
    }
}