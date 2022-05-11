<?php

namespace CsvTransform;

class OutputRule
{
    public function __construct(
        public readonly string $name,
        private TypeCaster $caster,
        private Transform $transform
    ) {}

    /**
     * Returns the output value.
     *
     * @param array $variableMap
     *
     * @return mixed
     * @throws Exception\CasterException
     * @throws Exception\TransformException
     */
    public function value(array $variableMap): mixed
    {
        return $this->caster->cast($this->transform->result($variableMap));
    }
}