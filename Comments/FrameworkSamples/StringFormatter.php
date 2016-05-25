<?php

namespace Common\Localisation\Formatter;

/**
 * Class StringFormatter
 * @package Common\Localisation\Formatter
 */
abstract class StringFormatter
{
    protected $pattern;

    /**
     * @var string Regexp to be applied on $pattern
     * Matches on e.g. {value1} {value2}
     */
    protected $regexp = '/({(\w+)})/';

    // ...
}
