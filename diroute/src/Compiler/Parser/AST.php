<?php

namespace Diroute\Compiler\Parser;

use Diroute\Compiler\Parser\Node\RootNode;

readonly class AST
{
    public function __construct(
        public RootNode $root
    ) {}
}
