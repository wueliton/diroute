<?php

namespace Diroute\CssEngine\Rules;

use Diroute\CssEngine\Contract\RuleInterface;
use Diroute\CssEngine\Parser\UtilityCandidate;

class BorderRules implements RuleInterface
{
    private array $borderWidths = [
        ''  => '1px',
        '0' => '0px',
        '2' => '2px',
        '4' => '4px',
        '8' => '8px',
    ];

    private array $radii = [
        'none' => '0px',
        'sm'   => '0.125rem',
        ''     => '0.25rem',
        'md'   => '0.375rem',
        'lg'   => '0.5rem',
        'xl'   => '0.75rem',
        '2xl'  => '1rem',
        '3xl'  => '1.5rem',
        'full' => '9999px',
    ];

    /**
     * Mapeamento direto por rawClass exata
     */
    private array $borderStyles = [
        'border-solid'  => 'border-style: solid;',
        'border-dashed' => 'border-style: dashed;',
        'border-dotted' => 'border-style: dotted;',
        'border-double' => 'border-style: double;',
        'border-none'   => 'border-style: none;',
    ];

    public function supports(UtilityCandidate $candidate): bool
    {
        $raw = $candidate->rawClass;
        $prefix = $candidate->utilityPrefix;

        // 1. PRIMEIRO: Checa se é um estilo exato (border-solid, border-dashed, etc)
        if (isset($this->borderStyles[$raw])) {
            return true;
        }

        // 2. SEGUNDO: Checa se é arredondamento (rounded, rounded-lg...)
        if ($prefix === 'rounded' || str_starts_with($raw, 'rounded-')) {
            return true;
        }

        // 3. TERCEIRO: Checa larguras de borda (evitando colisão com cores)
        // Se o prefixo for border, border-t, etc., e NÃO for um estilo
        return in_array($prefix, ['border', 'border-t', 'border-r', 'border-b', 'border-l', 'border-x', 'border-y'], true);
    }

    public function generateCss(UtilityCandidate $candidate): string
    {
        $raw = $candidate->rawClass;

        // 1. Estilos de Borda têm Prioridade Absoluta
        if (isset($this->borderStyles[$raw])) {
            return $this->borderStyles[$raw];
        }

        // 2. Arredondamento (Rounded)
        if ($candidate->utilityPrefix === 'rounded' || str_starts_with($raw, 'rounded-')) {
            return $this->generateRadiusCss($candidate);
        }

        // 3. Larguras de Borda (Widths)
        return $this->generateWidthCss($candidate);
    }

    private function generateRadiusCss(UtilityCandidate $candidate): string
    {
        if ($candidate->rawClass === 'rounded') {
            return "border-radius: {$this->radii['']};";
        }

        $value = $candidate->isArbitrary
            ? trim($candidate->value, '[]')
            : ($this->radii[$candidate->value] ?? null);

        if ($value === null) {
            return '';
        }

        $raw = $candidate->rawClass;

        if (str_starts_with($raw, 'rounded-t-'))  return "border-top-left-radius: {$value}; border-top-right-radius: {$value};";
        if (str_starts_with($raw, 'rounded-r-'))  return "border-top-right-radius: {$value}; border-bottom-right-radius: {$value};";
        if (str_starts_with($raw, 'rounded-b-'))  return "border-bottom-left-radius: {$value}; border-bottom-right-radius: {$value};";
        if (str_starts_with($raw, 'rounded-l-'))  return "border-top-left-radius: {$value}; border-bottom-left-radius: {$value};";
        if (str_starts_with($raw, 'rounded-tl-')) return "border-top-left-radius: {$value};";
        if (str_starts_with($raw, 'rounded-tr-')) return "border-top-right-radius: {$value};";
        if (str_starts_with($raw, 'rounded-br-')) return "border-bottom-right-radius: {$value};";
        if (str_starts_with($raw, 'rounded-bl-')) return "border-bottom-left-radius: {$value};";

        return "border-radius: {$value};";
    }

    private function generateWidthCss(UtilityCandidate $candidate): string
    {
        if ($candidate->rawClass === 'border') {
            return 'border-width: 1px;';
        }

        $value = $candidate->isArbitrary
            ? trim($candidate->value, '[]')
            : ($this->borderWidths[$candidate->value] ?? null);

        if ($value === null) {
            return '';
        }

        $prefix = $candidate->utilityPrefix;

        return match ($prefix) {
            'border'   => "border-width: {$value};",
            'border-x' => "border-left-width: {$value}; border-right-width: {$value};",
            'border-y' => "border-top-width: {$value}; border-bottom-width: {$value};",
            'border-t' => "border-top-width: {$value};",
            'border-r' => "border-right-width: {$value};",
            'border-b' => "border-bottom-width: {$value};",
            'border-l' => "border-left-width: {$value};",
            default    => ''
        };
    }
}
