<?php

namespace CsvTransform\Transform\Decorator;

use CsvTransform\Exception\DecoratorException;

class MathRound extends \CsvTransform\Transform\Decorator\AbstractDecorator
{
    public const COMMAND_NAME = 'M_ROUND';

    /**
     * @inheritDoc
     */
    protected function decorate(string $input, array $argv): string
    {
        $nonNumericTerms = array_filter([$input, ...$argv], fn($item) => !is_numeric($item));
        if ($nonNumericTerms) {
            throw new DecoratorException('Non-numeric terms encountered: ' . implode(',', $nonNumericTerms));
        }

        return round($input, $argv[0]);
    }

    /**
     * @inheritDoc
     */
    protected function requiredArgumentCount(): int
    {
        return 1;
    }
}