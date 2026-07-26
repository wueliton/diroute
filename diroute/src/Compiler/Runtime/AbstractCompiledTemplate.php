<?php

namespace Diroute\Compiler\Runtime;

abstract class AbstractCompiledTemplate
{
    /**
     * Método principal que o código compilado sobrescreve
     */
    abstract public function display(array $context): void;

    /**
     * Utilitário para escapar variáveis HTML de forma segura (XSS)
     */
    protected function escape(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
