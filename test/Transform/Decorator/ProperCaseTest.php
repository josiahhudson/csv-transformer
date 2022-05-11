<?php

namespace CsvTransform\Test\Transform\Decorator;

use CsvTransform\Transform\Decorator\TitleCase;
use CsvTransform\Transform\DecoratorArguments;
use CsvTransform\Transform\LiteralValue;
use PHPUnit\Framework\TestCase;

class ProperCaseTest extends TestCase
{
    public function testResult_happyPath_valuesAppended()
    {
        $transform = new LiteralValue('the quick, brOWn:fox jumped!');
        $args = new DecoratorArguments([]);

        $decorator = new TitleCase($transform, $args);

        $this->assertSame('The Quick, Brown:fox Jumped!', $decorator->result([]));
    }
}
