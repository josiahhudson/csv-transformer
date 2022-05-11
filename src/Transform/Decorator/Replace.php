<?php

namespace CsvTransform\Transform\Decorator;

use CsvTransform\Transform\Decorator\AbstractDecorator;

class Replace extends AbstractDecorator
{
    public const COMMAND_NAME = 'REPLACE';

    protected function decorate(string $input, array $argv): string
    {
        return str_replace($argv[0], $argv[1], $input);
    }

    protected function requiredArgumentCount(): int
    {
        return 2;
    }
}