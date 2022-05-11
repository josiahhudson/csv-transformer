<?php

namespace CsvTransform\Transform\Decorator;

use CsvTransform\Exception\DecoratorException;

class UpperCase extends AbstractDecorator
{
    const COMMAND_NAME = 'UPPER_CASE';

    /**
     * @inheritDoc
     */
    protected function decorate(string $input, array $argv): string
    {
        return strtoupper($input);
    }

    /**
     * @inheritDoc
     */
    protected function requiredArgumentCount(): int
    {
        return 0;
    }
}