<?php

namespace CsvTransform\FieldParseRule;

use CsvTransform\Exception\FieldParseRuleException;
use CsvTransform\FieldParseRule;

class RegexParse implements FieldParseRule
{
    public function __construct(
        private int $fieldNumber,
        private string $regex
    ) {}


    /**
     * Returns map of variable name => extracted value
     *
     * This rule will also return number keys which should be ignored.
     * This is due to the way the PCRE extension works, and can't be helped
     * without wasting processing time filtering them out.  As numbers aren't
     * valid variable names this should be safe to leave in place.
     *
     * @param array $fieldSet
     *
     * @return array
     * @throws FieldParseRuleException
     */
    public function parse(array $fieldSet): array
    {
        if (!array_key_exists($this->fieldNumber, $fieldSet)) {
            throw new FieldParseRuleException("Field not found: $this->fieldNumber");
        }

        $field = $fieldSet[$this->fieldNumber];
        $sucess = @preg_match($this->regex, $field, $matches, PREG_UNMATCHED_AS_NULL);

        if (preg_last_error()) {
            throw new FieldParseRuleException('Regex engine returned error: ' . preg_last_error_msg());
        } elseif ($sucess === 0) {
            throw new FieldParseRuleException("Failed to parse field $this->fieldNumber: '$field'");
        }


        return $matches;
    }
}