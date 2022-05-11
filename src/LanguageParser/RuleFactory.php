<?php

namespace CsvTransform\LanguageParser;

use CsvTransform\Exception\ParseException;
use CsvTransform\FieldParseRule;
use CsvTransform\FieldParseRule\RegexParse;
use CsvTransform\FieldParseRule\SimpleAssignment;
use CsvTransform\OutputRule;
use CsvTransform\Transform;
use CsvTransform\Transform\Decorator;
use CsvTransform\Transform\DecoratorArguments;
use CsvTransform\Transform\LiteralValue;
use CsvTransform\Transform\Variable;
use CsvTransform\TypeCaster;

class RuleFactory
{
    private array $decoratorMap = [];
    private array $casterMap = [];

    /**
     * This should be the class name of a Decorator
     *
     * @param string $className
     *
     * @return void
     */
    public function registerDecorator(string $className): void
    {
        // TODO: This is a bit hokey.  Perhaps throw exception if class doesn't exist?
        // TODO: Use reflection to ensure class is Decorator?

        $this->decoratorMap[$className::COMMAND_NAME] = $className;
    }

    /**
     * This should be the class name of a TypeCaster
     *
     * @param string $className
     *
     * @return void
     */
    public function registerCaster(string $className): void
    {
        // TODO: This is a bit hokey.  Perhaps throw exception if class doesn't exist?
        // TODO: Use reflection to ensure class is TypeCaster?

        $this->casterMap[$className::CAST_NAME] = $className;
    }

    /**
     * Returns the names of the registered decorators.
     *
     * @return string[]
     */
    public function getRegistredTransforms(): array
    {
        return array_keys($this->decoratorMap);
    }

    /**
     * Returns the names of the registered decorators.
     *
     * @return string[]
     */
    public function getRegistredCasts(): array
    {
        return array_keys($this->casterMap);
    }

    /**
     * Return a parse rule as described by the spec.
     *
     * @param array $spec
     *
     * @return FieldParseRule
     */
    public function buildParseRule(array $spec): FieldParseRule
    {
        $column   = $spec['column_number'];
        $regex    = $spec['regex'] ?? null;
        $varName  = $spec['var_name'] ?? null;

        if ($regex) {
            $rule = new RegexParse($column, $regex);
        } else {
            $rule = new SimpleAssignment($column, $varName);
        }

        return $rule;
    }

    /**
     * Build an output rule as specified by parameters.
     *
     * @param string $name
     * @param string $cast
     * @param array  $transformationList
     *
     * @return OutputRule
     * @throws ParseException
     */
    public function buildOutputRule(string $name, string $cast, array $transformationList): OutputRule
    {
        $chain = null;
        foreach ($transformationList as ['transform' => $transformName, 'args' => $argList]) {
            if ($transformName === 'OUTPUT_RULE_START') {
                $chain = $this->argFactory($argList[0]);
            } else {
                $args = new DecoratorArguments(
                    array_map($this->argFactory(...), $argList)
                );

                $chain = $this->buildCommand($transformName, $chain, $args);
            }
        }

        return new OutputRule(
            $name,
            $this->buildCaster($cast),
            $chain
        );
    }

    private function buildCommand(string $commandName, Transform $transform, DecoratorArguments $args): Decorator
    {
        $className = $this->decoratorMap[$commandName] ?? null;
        if ($className === null) {
            throw new ParseException("Unknown command '$commandName'");
        }

        // A bit of dynamic evil...
        // If above TODOs were implemented, this could be made safer.
        return new $className($transform, $args);
    }

    private function buildCaster(string $name): TypeCaster
    {
        $className = $this->casterMap[$name] ?? null;
        if ($className === null) {
            throw new ParseException("Unknown cast '$name'");
        }

        // A bit of dynamic evil...
        return new $className;
    }

    private function argFactory(string $valueStr): Transform
    {
        if ($valueStr[0] === '"') {
            // trim off the quotes:
            $value = new LiteralValue(substr($valueStr,1, -1));
        } else {
            $value = new Variable($valueStr);
        }

        return $value;
    }

}