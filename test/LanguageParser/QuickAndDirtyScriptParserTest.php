<?php

namespace CsvTransform\Test\LanguageParser;

use CsvTransform\Exception\ParseException;
use CsvTransform\FieldParseRule\RegexParse;
use CsvTransform\FieldParseRule\SimpleAssignment;
use CsvTransform\LanguageParser\QuickAndDirtyScriptParser;
use CsvTransform\LanguageParser\RuleFactory;
use CsvTransform\OutputRule;
use CsvTransform\Program;
use CsvTransform\Transform\Decorator\Append;
use CsvTransform\Transform\Decorator\MathRound;
use CsvTransform\Transform\Decorator\Replace;
use CsvTransform\Transform\Decorator\TitleCase;
use CsvTransform\Transform\DecoratorArguments;
use CsvTransform\Transform\LiteralValue;
use CsvTransform\Transform\Variable;
use CsvTransform\TypeCaster\IntegerCaster;
use CsvTransform\TypeCaster\NumberCaster;
use CsvTransform\TypeCaster\StringCaster;
use PHPUnit\Framework\TestCase;

class QuickAndDirtyScriptParserTest extends TestCase
{
    private function buildParser(): QuickAndDirtyScriptParser
    {
        $ruleFactory = new RuleFactory;
        $ruleFactory->registerDecorator(Append::class);
        $ruleFactory->registerDecorator(MathRound::class);
        $ruleFactory->registerDecorator(Replace::class);
        $ruleFactory->registerDecorator(TitleCase::class);

        $ruleFactory->registerCaster(IntegerCaster::class);
        $ruleFactory->registerCaster(NumberCaster::class);
        $ruleFactory->registerCaster(StringCaster::class);

        return new QuickAndDirtyScriptParser($ruleFactory);
    }

    public function testParse_happyPath_itAllWorks()
    {
        // Let's not use mocks to spy.  Let's just see if it all works together.
        $parser = $this->buildParser();

        $actual = $parser->parse(/** @lang text */ <<<SCRIPT
        # Make sure comments and empty lines are ignored
        
        Parse:
            # simple assignment
            0 AS field_a
            
            # regex assignment
            1 AS /^(?<field_b>[^-]+)-(?<field_c>.+)$/
            
        Output:
            # Test appends and replacement
            str Out field 1: field_a + field_b | REPLACE field_b field_c
            
            # Test literal strings
            int Out field 2: "start_" + field_a + "_end" | REPLACE "_" field_c
        SCRIPT);

        $expected = new Program;

        // 0 AS field_a
        $expected->addParseRule(new SimpleAssignment(0, 'field_a'));

        // 1 AS /^(?<field_b>[^-]+)-(?<field_c>.+)$/
        $expected->addParseRule(new RegexParse(1, '/^(?<field_b>[^-]+)-(?<field_c>.+)$/'));

        // str Out field 1: field_a + field_b | REPLACE field_b field_c
        $expected->addOutputRule(
            new OutputRule(
                'Out field 1',
                new StringCaster,
                new Replace(
                    new Append(
                        new Variable('field_a'),
                        new DecoratorArguments([new Variable('field_b')])
                    ),
                    new DecoratorArguments([
                        new Variable('field_b'),
                        new Variable('field_c')
                    ])
                )
            )
        );

        // int Out field 2: "start_" + field_a + "_end" | REPLACE "_" field_c
        $expected->addOutputRule(
            new OutputRule(
                'Out field 2',
                new IntegerCaster,
                new Replace(
                    new Append(
                        new Append(
                            new LiteralValue('start_'),
                            new DecoratorArguments([new Variable('field_a')])
                        ),
                        new DecoratorArguments([new LiteralValue('_end')])
                    ),
                    new DecoratorArguments([
                        new LiteralValue('_'),
                        new Variable('field_c')
                    ])
                )
            )
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider badScriptProvider
     */
    public function testParse_unhappyPaths_exceptionsAreThrown(string $script) {
        $this->expectException(ParseException::class);

        $parser = $this->buildParser();
        $parser->parse($script);
    }

    public function badScriptProvider(): array
    {
        return [
            'Missing Parse-block header' => [
                <<<SCRIPT
                0 AS test
                SCRIPT
            ],
            'Invalid field parse line_missing AS' => [
                <<<SCRIPT
                Parse:
                0 test
                SCRIPT
            ],
            'Invalid field parse line_bad column spec' => [
                <<<SCRIPT
                Parse:
                A AS test
                SCRIPT
            ],
            'Invalid field parse line_variable name' => [
                <<<SCRIPT
                Parse:
                0 AS Test
                SCRIPT
            ],
            'Invalid field parse line_malformed regex' => [
                <<<SCRIPT
                Parse:
                0 AS /regex~
                SCRIPT
            ],
            'Missing Output block header' => [
                <<<SCRIPT
                Parse:
                  0 AS test
                
                  int OutField: test
                SCRIPT
            ],
            'Missing cast type' => [
                <<<SCRIPT
                Parse:
                  0 AS test
                Output:
                  OutField: test
                SCRIPT
            ],
            'Unregistered cast type' => [
                <<<SCRIPT
                Parse:
                  0 AS test
                Output:
                  bad OutField: test
                SCRIPT
            ],
            'Illegal output field name' => [
                <<<SCRIPT
                Parse:
                  0 AS test
                Output:
                  int Colons:Are:Not:allowed: test
                SCRIPT
            ],
            'Missing colon' => [
                <<<SCRIPT
                Parse:
                  0 AS test
                Output:
                  int Output field name test + test
                SCRIPT
            ],
            'Transform chain missing starting value' => [
                <<<SCRIPT
                Parse:
                  0 AS test
                Output:
                  int Output field name: REPLACE " " ""
                SCRIPT
            ],
            'Missing transform pipe' => [
                <<<SCRIPT
                Parse:
                  0 AS test
                Output:
                  int Output field name: test REPLACE " " ""
                SCRIPT
            ],
        ];
    }
}
