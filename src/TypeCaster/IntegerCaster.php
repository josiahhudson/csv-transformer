<?php

namespace CsvTransform\TypeCaster;

use CsvTransform\Exception\CasterException;
use CsvTransform\TypeCaster;

class IntegerCaster implements TypeCaster
{
    public const CAST_NAME = 'int';

    /**
     * @inheritDoc
     */
    public function cast(string $value): mixed
    {
        $castValue = (int) $value;
        if ($value != $value) {
            throw new CasterException("Value '$value' cannot be cast to int!");
        }

        return $castValue;
    }
}