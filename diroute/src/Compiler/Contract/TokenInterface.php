<?php

namespace Diroute\Compiler\Contract;

interface TokenInterface
{
    public function getType(): mixed;
    public function getValue(): string;
    public function getLine(): int;
    public function getColumn(): int;
}
