<?php

namespace CsvTransform\LanguageParser;

use CsvTransform\Exception\ParseException;
use CsvTransform\LanguageParser;
use CsvTransform\Program;

class QuickAndDirtyScriptParser implements LanguageParser
{

    /**
     * Performs an explode, and trims all resultant fields.
     *
     * @param string $delim
     * @param string $data
     *
     * @return array
     * @see \explode()
     * @see \trim()
     */
    private function trimmedExplode(string $delim, string $data): array
    {
        return array_map('trim', explode($delim, $data));
    }

    /**
     * Parses a syntactically valid transform string.
     *
     * If there are issues, an exception with descriptive message is thrown.
     *
     * @param string $transformString
     *
     * @return array[]  List of records of the form: [
     *                      'transform' => Transform type,
     *                      'args'      => List of arguments
     *                  ]
     * @throws ParseException
     */
    private function parseTransformString(string $transformString): array
    {
        if (!$this->isValidTransformString($transformString)) {
            throw new ParseException("Unable to parse transform: '$transformString'");
        }

        // NOTE: The guard above verifies validity and order of tokens, so this "dumb" lexing is safe
        preg_match_all('/([A-Z_]+|"[^"]*"|[a-z_][a-z0-9_]*)/', $transformString, $matches);

        $transform = array_shift($matches[0]);

        return [
            'transform' => $transform,
            'args'      => $matches[0]
        ];
    }

    /**
     * Returns TRUE if string is syntactically valid, FALSE otherwise.
     *
     * @param string $string
     *
     * @return bool
     */
    private function isValidTransformString(string $string): bool
    {
        return preg_match('/^[A-Z_]+(?:\s+("[^"]*"|[a-z_][a-z0-9_]*))*$/', $string) === 1;
    }


    /**
     * Parses an output rule string.
     *
     * @param string $rule
     *
     * @return array [
     *                  'name' => (string) Output field name,
     *                  'cast' => (string) Output cast type
     *                  'transformationList' => (array) Parsed transformations.
     *               ]
     * @throws ParseException
     */
    private function parseOutputRule(string $rule): array
    {
        $rule = $this->applySyntacticSugarToOutputRule($rule);

        if (!preg_match(<<<REGEX
        /^
          (?<data_type>[a-z]+)
          \s+
          (?<field_name>[[:alnum:]_][^:]*)
          :
          \s+
          (?<transforms>.*)
        $/x
        REGEX,
            $rule,
            $matches)) {
            throw new ParseException("Unable to parse output rule: '$rule'");
        }

        // Add SELECT as first transform so all transformations are the same shape:
        $transformStringList = $this->trimmedExplode('|', $matches['transforms']);

        $transformSpecs = [];
        foreach ($transformStringList as $transformString) {
            $transformSpecs[] = $this->parseTransformString($transformString);
        }

        return [
            'name' => $matches['field_name'],
            'cast' => $matches['data_type'],
            'transformationList' => $transformSpecs
        ];
    }

    /**
     * Parses syntactically valid rule string.
     *
     * If there are issues, an exception with descriptive message is thrown.
     *
     * @param string $rule
     *
     * @return array
     * @throws ParseException
     */
    private function parseParseRule(string $rule): array
    {
        // TODO: Make sure this can't catastrophically backtrack.
        if (!preg_match('/^(?<column_number>\d+)\s+AS\s+(?:(?<var_name>[a-z_]+)|(?<regex>(.).*\g{-1}))$/',
            $rule,
            $matches)) {
            throw new ParseException("Unable to parse parse-rule: '$rule'");
        }

        return array_filter(
            array_intersect_key($matches, ['column_number' => true, 'regex' => true, 'var_name' => true]),
            fn($value) => $value !== ''
        );
    }

    /**
     * Expands syntactic sugar.
     *
     * @param string $outputRule
     *
     * @return string
     */
    private function applySyntacticSugarToOutputRule(string $outputRule): string
    {
        // NOTE: Add step variables are numbered to enforce ordering.
        //       It should be a slight pain to modify this section.

        // Insert OUTPUT_RULE_START token used to keep all transforms congruent
        $step0 = preg_replace('/^([^:]+): /', '\1: OUTPUT_RULE_START ', $outputRule);

        // Pre-process the +'s so "a + b" becomes "a | APPEND b":
        $step1 = str_replace('+', '| APPEND ', $step0);

        return $step1;
    }

    private function shouldSkipLine(string $line): bool
    {
        $trimmed = trim($line);

        return !$trimmed || $trimmed[0] === '#';
    }

    /**
     * Parses a FancyCsvTransform script into something resembling an AST.
     *
     * If there are issues, an exception with descriptive message is thrown.
     *
     * @param string $script
     *
     * @return array
     * @throws ParseException
     */
    public function parse(string $script): Program
    {
        $configLines = $this->trimmedExplode("\n", $script);

        $program = new Program;
        try {
            $state = 'start';
            foreach ($configLines as $lineNumber => $line) {

                if ($this->shouldSkipLine($line)) {
                    continue;
                }

                switch ($state) {
                    case 'start':
                        if ($line !== 'Parse:') {
                            throw new ParseException("invalid file format at line $lineNumber:\n$line");
                        }

                        $state = 'parse_rules';
                        break;
                    case 'parse_rules':
                        if ($line === 'Output:') {
                            $state = 'output_rules';
                            break;
                        }

                        $program->addParseRule(
                            $this->factory->buildParseRule(
                                $this->parseParseRule($line)
                            )
                        );

                        break;
                    case 'output_rules':
                        if ($line === '') {
                            $state = 'end';
                            break;
                        }


                        [
                            'name' => $name,
                            'cast' => $cast,
                            'transformationList' => $transformSpecs
                        ] = $this->parseOutputRule($line);

                        $program->addOutputRule(
                            $this->factory->buildOutputRule($name, $cast, $transformSpecs)
                        );
                }
            }
        } catch (ParseException $exception) {
            $outputLineNumber = $lineNumber + 1; // To humans, files start at line 1
            throw new ParseException("Error parsing script at line $outputLineNumber: {$exception->getMessage()}");
        }

        return $program;
    }

    public function __construct(
        private readonly RuleFactory $factory
    ) {}
}