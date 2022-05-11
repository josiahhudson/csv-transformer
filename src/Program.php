<?php

namespace CsvTransform;

class Program
{
    /**
     * @var FieldParseRule[]
     */
    private array $parseRules;

    /**
     * @var OutputRule[]
     */
    private array $outputRules;

    /**
     * Add parse rule.
     *
     * @param FieldParseRule $rule
     *
     * @return void
     */
    public function addParseRule(FieldParseRule $rule): void
    {
        $this->parseRules[] = $rule;
    }

    /**
     * Add Output rule.
     *
     * @param OutputRule $rule
     *
     * @return void
     */
    public function addOutputRule(OutputRule $rule): void
    {
        $this->outputRules[] = $rule;
    }

    /**
     * Processes record according to rules.
     *
     * @param array $record
     *
     * @return array
     * @throws Exception\CasterException
     * @throws Exception\FieldParseRuleException
     * @throws Exception\TransformException
     */
    public function processRecord(array $record): array
    {
        $variableMap = [];
        foreach ($this->parseRules as $rule) {
            $variableMap += $rule->parse($record);
        }

        $newRecord = [];
        foreach ($this->outputRules as $rule) {
            $newRecord[$rule->name] = $rule->value($variableMap);
        }

        return $newRecord;
    }
}