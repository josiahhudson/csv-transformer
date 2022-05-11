<?php

namespace CsvTransform;

use CsvTransform\Exception\TransformException;

interface Transform
{
    /**
     * Performs a transformation based on the variableMap and returns the result as a string.
     *
     * @param array $variableMap
     *
     * @return string
     * @throws TransformException
     */
    public function result(array $variableMap): string;
}