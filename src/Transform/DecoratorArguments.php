<?php

namespace CsvTransform\Transform;

use CsvTransform\Transform;

class DecoratorArguments
{
    private $args = [];

    /**
     * @param Transform[] $transformList
     *
     * @return void
     */
    public function __construct(array $transformList)
    {
        foreach ($transformList as $transform) {
            $this->addArgument($transform);
        }
    }

    /**
     * @param Transform $transform
     *
     * @return void
     */
    private function addArgument(Transform $transform): void
    {
        $this->args[] = $transform;
    }

    /**
     * Returns count of argument set.
     *
     * @return int
     * @todo PHP 8.1 readonly fields would be great here.
     */
    public function argc(): int
    {
        return count($this->args);
    }

    /**
     * Return transform at index.
     *
     * @param int $index
     *
     * @return Transform
     * @throws \OutOfBoundsException if bad index is given.
     */
    public function argv(int $index): Transform
    {
        return $this->args[$index] ?? throw new \OutOfBoundsException("Undefined argument $index.");
    }
}