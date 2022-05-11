<?php

namespace CsvTransform;

interface OutputWriter
{
    /**
     * Writes output in appropriate format.
     *
     * @param array $record
     *
     * @return int|bool  Number of mytes writen or FALSE on error.
     */
    public function write(array $record): int|bool;
}