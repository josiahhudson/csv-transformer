<?php

namespace CsvTransform\Transform;

use CsvTransform\Exception\TransformException;
use CsvTransform\Transform;

class Variable implements Transform
{
    public function __construct(
        private string $name
    ) {}

    /**
     * @inheritDoc
     */
    public function result(array $variableMap): string
    {
        $value = $variableMap[$this->name] ?? null;
        if ($value === null) {
            throw new TransformException("Undefined variable '$this->name'.");
        }

        return $value;
    }
}