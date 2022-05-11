<?php

namespace CsvTransform;

use CsvTransform\Exception\ParseException;

interface LanguageParser
{
    /**
     * Parses a FancyCsvTransform script into something resembling an AST.
     *
     * If there are issues, an exception with descriptive message is thrown.
     *
     * @param string $script
     *
     * @return array
     * @throws ParseException
     */
    public function parse(string $script): Program;
}