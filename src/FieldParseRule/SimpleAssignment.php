<?php

namespace CsvTransform\FieldParseRule;

use CsvTransform\Exception\FieldParseRuleException;
use CsvTransform\FieldParseRule;

class SimpleAssignment implements FieldParseRule
{
    /**
     *
     *
     * @param int    $fieldNumber
     * @param string $variableName
     */
    public function __construct(
        private int $fieldNumber,
        private string $variableName
    ) {}

    /**
     * @inheritDoc
     */
    public function parse(array $fieldSet): array
    {
        if (!array_key_exists($this->fieldNumber, $fieldSet)) {
            throw new FieldParseRuleException("Field not found: $this->fieldNumber");
        }

        return [$this->variableName => $fieldSet[$this->fieldNumber]];
    }
}