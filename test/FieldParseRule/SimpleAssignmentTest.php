<?php

namespace CsvTransform\Test\FieldParseRule;

use CsvTransform\Exception\FieldParseRuleException;
use CsvTransform\FieldParseRule\SimpleAssignment;
use PHPUnit\Framework\TestCase;

class SimpleAssignmentTest extends TestCase
{

    private $testFields = [
        0 => 'foo',
        1 => 'bar'
    ];

    public function testParse_happyPath_correctValuesReturned()
    {
        $rule = new SimpleAssignment(1, 'name');

        $this->assertSame(
            ['name' => 'bar'],
            $rule->parse($this->testFields)
        );
    }

    public function testParse_badFieldNumber_exceptionThrown()
    {
        $this->expectException(FieldParseRuleException::class);
        $rule = new SimpleAssignment(5, 'name');
        $rule->parse($this->testFields);
    }
}
