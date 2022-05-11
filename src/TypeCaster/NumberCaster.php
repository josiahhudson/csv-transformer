<?php

namespace CsvTransform\TypeCaster;

use CsvTransform\Exception\CasterException;
use CsvTransform\TypeCaster;

class NumberCaster implements TypeCaster
{
    public const CAST_NAME = 'num';

    /**
     * @inheritDoc
     */
    public function cast(string $value): mixed
    {
        if (!is_numeric($value)) {
            throw new CasterException("Value '$value' cannot be cast to num!");
        }

        return $value;
    }
}