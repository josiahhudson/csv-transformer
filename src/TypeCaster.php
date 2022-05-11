<?php

namespace CsvTransform;

use CsvTransform\Exception\CasterException;

interface TypeCaster
{
    /**
     * Implement this for dynamic registry.
     */
    public const CAST_NAME = '';

    /**
     * Returns cast value.
     *
     * @param string $value
     *
     * @return mixed
     * @throws CasterException
     */
    public function cast(string $value): mixed;
}