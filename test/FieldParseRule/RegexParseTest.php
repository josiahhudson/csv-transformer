<?php

namespace CsvTransform\Test\FieldParseRule;

use CsvTransform\Exception\FieldParseRuleException;
use CsvTransform\FieldParseRule\RegexParse;
use PHPUnit\Framework\TestCase;

class RegexParseTest extends TestCase
{
    private $testFields = [
        0 => 'aaabbb',
        1 => 'xyz'
    ];

    public function testParse_happyPath_allValuesExtracted()
    {
        $rule = new RegexParse(0, '/(?<a_list>a+)(?<b_list>b+)/');

        $return = $rule->parse($this->testFields);
        $this->stripNumberedKeys($return, 3);

        $this->assertSame([
            'a_list' => 'aaa',
            'b_list' => 'bbb'
        ],
            $return
        );
    }

    public function testParse_badRecord_exceptionThrown()
    {
        $this->expectException(FieldParseRuleException::class);

        $rule = new RegexParse(1, '/(?<a_list>a+)(?<b_list>b+)/');

        $return = $rule->parse($this->testFields);
    }

    public function testParse_badRegex_exceptionThrown()
    {
        $this->expectException(FieldParseRuleException::class);

        $rule = new RegexParse(1, '/(?');

        $return = $rule->parse($this->testFields);
    }

    /**
     * Hokey function to get around PCRE extensions integer key returns.
     *
     * @param $array
     * @param $limit
     *
     * @return void
     */
    private function stripNumberedKeys(&$array, $limit)
    {
        for($i = 0; $i < $limit; $i++) {
            unset($array[$i]);
        }
    }
}
