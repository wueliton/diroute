<?php

namespace Diroute\Compiler\Generator;

class PHPBuffer
{
    private string $buffer = '';
    private int $indentLevel = 0;

    public function indent(int $levels = 1): self
    {
        $this->indentLevel += $levels;
        return $this;
    }

    public function outdent(int $levels = 1): self
    {
        $this->indentLevel = \max(0, $this->indentLevel - $levels);
        return $this;
    }

    public function write(string $code): self
    {
        $this->buffer .= $code;
        return $this;
    }

    public function writeLine(string $code = ''): self
    {
        if ($code !== '') {
            $this->buffer .= \str_repeat('    ', $this->indentLevel) . $code . "\n";
        } else {
            $this->buffer .= "\n";
        }
        return $this;
    }

    public function getContents(): string
    {
        return $this->buffer;
    }
}
