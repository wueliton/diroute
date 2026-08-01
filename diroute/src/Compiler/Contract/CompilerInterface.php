<?php

namespace Diroute\Compiler\Contract;

use Diroute\Compiler\Parser\AST;

interface CompilerInterface
{
    public function compile(string $source): string;
    public function parseToAst(string $source): AST;
    public function generateCodeFromAst(AST $optimizedAst): string;
}
