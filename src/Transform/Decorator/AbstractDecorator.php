<?php

namespace CsvTransform\Transform\Decorator;

use CsvTransform\Exception\DecoratorException;
use CsvTransform\Transform;
use CsvTransform\Transform\Decorator;
use CsvTransform\Transform\DecoratorArguments;

abstract class AbstractDecorator implements Decorator
{
    private int $requiredArgc;

    public function __construct(
        protected Transform $transform,
        protected DecoratorArguments $args
    ) {
        $argc = $this->args->argc();
        $this->requiredArgc = $this->requiredArgumentCount();
        if ($argc !== $this->requiredArgc) {
            // TODO: This could probably be better.
            throw new \ArgumentCountError("Bad argument count.  Needs $this->requiredArgc, $argc given!");
        }
    }

    /**
     * @inheritDoc
     */
    public function result(array $variableMap): string
    {
        $argv = [];
        for ($i = 0; $i < $this->requiredArgc; $i++) {
            $argv[] = $this->args->argv($i)->result($variableMap);
        }

        return $this->decorate(
            $this->transform->result($variableMap),
            $argv
        );
    }

    /**
     * Implement this method to perform transform.
     *
     * The parent class will take care of resolving and passing in the
     * appropriate arguments.
     *
     * @param string $input Result to be decorated.
     * @param array  $argv  List of arguments the command was passed.
     *
     * @return string
     * @throws DecoratorException  If decorator encounters an error.
     */
    abstract protected function decorate(string $input, array $argv): string;

    /**
     * Tells the class how many arguments it gets passed.
     *
     * @return int
     */
    abstract protected function requiredArgumentCount(): int;
}