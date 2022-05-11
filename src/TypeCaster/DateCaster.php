<?php

namespace CsvTransform\TypeCaster;

use CsvTransform\Exception\CasterException;
use CsvTransform\TypeCaster;
use DateTime;
use Exception;

class DateCaster implements TypeCaster
{
    public const CAST_NAME = 'date';

    /**
     * @inheritDoc
     */
    public function cast(string $value): mixed
    {
        try {
            $datetime = new DateTime($value);
        } catch (Exception) {
            throw new CasterException("Unable to cast value to date: '$value'");
        }

        return $datetime->format('Y-m-d');
    }
}