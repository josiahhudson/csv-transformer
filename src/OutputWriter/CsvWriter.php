<?php

namespace CsvTransform\OutputWriter;

use CsvTransform\OutputWriter;

class CsvWriter implements OutputWriter
{
    public function __construct(
        protected $fileHandle
    ) {}


    /**
     * @inheritDoc
     */
    public function write(array $record): int|bool
    {
        return fputcsv($this->fileHandle, array_values($record));
    }
}