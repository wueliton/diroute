<?php

namespace Diroute\Compiler\Contract;

interface DirectiveInterface
{
    public function getName(): string;
    public function hasBlock(): bool;
    public function requiresArgument(): bool;
}
