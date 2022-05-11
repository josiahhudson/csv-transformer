<?php

namespace CsvTransform\Transform\Decorator;

use CsvTransform\Transform\Decorator\AbstractDecorator;

class TitleCase extends AbstractDecorator
{
    public const COMMAND_NAME = 'TITLE_CASE';

    protected function decorate(string $input, array $argv): string
    {
        return mb_convert_case($input, MB_CASE_TITLE);
    }

    protected function requiredArgumentCount(): int
    {
        return 0;
    }
}