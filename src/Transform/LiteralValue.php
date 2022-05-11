<?php

namespace CsvTransform\Transform;

use CsvTransform\Transform;

class LiteralValue implements Transform
{
    public function __construct(
        private string $value
    ) {}

    /**
     * @inheritDoc
     */
    public function result(array $variableMap): string
    {
        return $this->value;
    }
}