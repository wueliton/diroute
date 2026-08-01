<?php

namespace Diroute\CssEngine\Rules;

use Diroute\CssEngine\Contract\RuleInterface;
use Diroute\CssEngine\Parser\UtilityCandidate;

class CursorRules implements RuleInterface
{
    private array $cursors = [
        'auto'         => 'auto',
        'default'      => 'default',
        'pointer'      => 'pointer',
        'text'         => 'text',
        'move'         => 'move',
        'grab'         => 'grab',
        'grabbing'     => 'grabbing',
        'wait'         => 'wait',
        'progress'     => 'progress',
        'help'         => 'help',
        'crosshair'    => 'crosshair',
        'not-allowed'  => 'not-allowed',
        'none'         => 'none',
        'zoom-in'      => 'zoom-in',
        'zoom-out'     => 'zoom-out',
        'col-resize'   => 'col-resize',
        'row-resize'   => 'row-resize',
        'n-resize'     => 'n-resize',
        'e-resize'     => 'e-resize',
        's-resize'     => 's-resize',
        'w-resize'     => 'w-resize',
        'ne-resize'    => 'ne-resize',
        'nw-resize'    => 'nw-resize',
        'se-resize'    => 'se-resize',
        'sw-resize'    => 'sw-resize',
        'ew-resize'    => 'ew-resize',
        'ns-resize'    => 'ns-resize',
        'nesw-resize'  => 'nesw-resize',
        'nwse-resize'  => 'nwse-resize',
    ];

    public function supports(UtilityCandidate $candidate): bool
    {
        return $candidate->utilityPrefix === 'cursor';
    }

    public function generateCss(UtilityCandidate $candidate): string
    {
        $value = $candidate->isArbitrary
            ? trim($candidate->value, '[]')
            : ($this->cursors[$candidate->value] ?? null);

        if ($value === null) {
            return '';
        }

        return "cursor: {$value};";
    }
}
