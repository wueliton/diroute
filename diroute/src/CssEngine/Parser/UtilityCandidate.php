<?php

namespace Diroute\CssEngine\Parser;

class UtilityCandidate
{
    public function __construct(
        public string $rawClass,
        public array $variants,
        public string $utilityPrefix,
        public string $value,
        public bool $isArbitrary = false
    ) {}

    public static function parse(string $rawClass): self
    {
        $parts = explode(':', $rawClass);
        $rawUtility = array_pop($parts);
        $variants = $parts;

        $dashPos = strpos($rawUtility, '-');

        if ($dashPos === false) {
            $prefix = $rawUtility;
            $value = '';
        } else {
            $prefix = substr($rawUtility, 0, $dashPos);
            $value = substr($rawUtility, $dashPos + 1);
        }

        $isArbitrary = str_starts_with($value, '[') && str_ends_with($value, ']');

        return new self($rawClass, $variants, $prefix, $value, $isArbitrary);
    }
}
