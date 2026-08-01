<?php

namespace Diroute\CssEngine\Rules;

use Diroute\CssEngine\Contract\RuleInterface;
use Diroute\CssEngine\Parser\UtilityCandidate;

class SpacingRules implements RuleInterface
{
    private array $spacingScale = [
        'none' => '0',
        'xxs'  => '0.25rem', // 4px
        'xs'   => '0.5rem',  // 8px
        'sm'   => '0.75rem', // 12px
        'md'   => '1rem',    // 16px
        'lg'   => '1.5rem',  // 24px
        'xl'   => '2rem',    // 32px
        '2xl'  => '2.5rem',  // 40px
        '3xl'  => '3rem',    // 48px
        '4xl'  => '3.5rem',  // 56px
        '5xl'  => '4rem',    // 64px
        '6xl'  => '5rem',    // 80px
        '7xl'  => '6rem',    // 96px
        '8xl'  => '8rem',    // 128px
    ];

    public function supports(UtilityCandidate $candidate): bool
    {
        return in_array($candidate->utilityPrefix, [
            // Padding
            'p',
            'px',
            'py',
            'pt',
            'pr',
            'pb',
            'pl',
            'ps',
            'pe',

            // Margin
            'm',
            'mx',
            'my',
            'mt',
            'mr',
            'mb',
            'ml',
            'ms',
            'me',
        ], true);
    }

    public function generateCss(UtilityCandidate $candidate): string
    {
        $value = $candidate->isArbitrary
            ? trim($candidate->value, '[]')
            : ($this->spacingScale[$candidate->value] ?? ($candidate->value === 'auto' ? 'auto' : null));

        if ($value === null) {
            return '';
        }

        return match ($candidate->utilityPrefix) {
            // Padding
            'p'  => "padding: {$value};",
            'px' => "padding-left: {$value}; padding-right: {$value};",
            'py' => "padding-top: {$value}; padding-bottom: {$value};",
            'pt' => "padding-top: {$value};",
            'pr' => "padding-right: {$value};",
            'pb' => "padding-bottom: {$value};",
            'pl' => "padding-left: {$value};",
            'ps' => "padding-inline-start: {$value};",
            'pe' => "padding-inline-end: {$value};",

            // Margin
            'm'  => "margin: {$value};",
            'mx' => "margin-left: {$value}; margin-right: {$value};",
            'my' => "margin-top: {$value}; margin-bottom: {$value};",
            'mt' => "margin-top: {$value};",
            'mr' => "margin-right: {$value};",
            'mb' => "margin-bottom: {$value};",
            'ml' => "margin-left: {$value};",
            'ms' => "margin-inline-start: {$value};",
            'me' => "margin-inline-end: {$value};",

            default => '',
        };
    }
}
