<?php

namespace Diroute\Compiler\Contract;

interface CompilerInterface
{
    public function compile(string $source): string;
}
