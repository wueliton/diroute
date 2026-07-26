<?php

namespace Example\AST;

class InterpolatedValueNode
{
    public function __construct(
        public array $parts,
    ) {}
}
