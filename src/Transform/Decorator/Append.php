<?php

namespace CsvTransform\Transform\Decorator;

class Append extends AbstractDecorator
{
    public const COMMAND_NAME = 'APPEND';

    protected function decorate(string $input, array $argv): string
    {
        return $input . $argv[0];
    }

    protected function requiredArgumentCount(): int
    {
        return 1;
    }
}