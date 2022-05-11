<?php

namespace CsvTransform\Test\Transform\Decorator;

use CsvTransform\Transform\Decorator\Append;
use PHPUnit\Framework\TestCase;
use CsvTransform\Transform\{LiteralValue, DecoratorArguments};

class AppendTest extends TestCase
{
    public function testResult_happyPath_valuesAppended()
    {
        $transform = new LiteralValue('aaa');

        $args = new DecoratorArguments([
            new LiteralValue('bbb')
        ]);

        $decorator = new Append($transform, $args);

        $this->assertSame('aaabbb', $decorator->result([]));
    }
}
