<?php

namespace CsvTransform\Transform;

use CsvTransform\Transform;

interface Decorator extends Transform
{
    /**
     * This should be implemented for registration
     */
    public const COMMAND_NAME = '';

    /**
     * Decorators are built by a dynamic factory, so constructors should conform.
     *
     * @param Transform          $transform
     * @param DecoratorArguments $args
     */
    public function __construct(Transform $transform, DecoratorArguments $args);
}