<?php

namespace CsvTransform\TypeCaster;

class StringCaster implements \CsvTransform\TypeCaster
{
    public const CAST_NAME = 'str';

    /**
     * @inheritDoc
     */
    public function cast(string $value): mixed
    {
        return $value;
    }
}