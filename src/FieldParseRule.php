<?php

namespace CsvTransform;

use CsvTransform\Exception\FieldParseRuleException;

interface FieldParseRule
{
    /**
     * Returns map of variable name => extracted value
     *
     * @param string[] $fieldSet
     *
     * @return array
     * @throws FieldParseRuleException
     */
    public function parse(array $fieldSet): array;
}