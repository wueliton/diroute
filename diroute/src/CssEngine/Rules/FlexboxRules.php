<?php

namespace Diroute\CssEngine\Rules;

use Diroute\CssEngine\Contract\RuleInterface;
use Diroute\CssEngine\Parser\UtilityCandidate;

class FlexboxRules implements RuleInterface
{
    /**
     * Tabela de escala de espaçamento padrão (padrão Tailwind/Diroute)
     */
    private array $spacingScale = [
        'none' => '0px',
        'xxs'  => '4px',
        'xs'   => '8px',
        'sm'   => '12px',
        'md'   => '16px',
        'lg'   => '24px',
        'xl'   => '32px',
        '2xl'  => '40px',
        '3xl'  => '48px',
        '4xl'  => '56px',
        '5xl'  => '64px',
        '6xl'  => '80px',
        '7xl'  => '96px',
        '8xl'  => '128px',
    ];

    /**
     * Mapeamento estático de utilidades simples
     */
    private array $staticMap = [
        // Display
        'flex'         => 'display: flex;',
        'inline-flex'  => 'display: inline-flex;',
        'grid'         => 'display: grid;',
        'inline-grid'  => 'display: inline-grid;',

        // Flex Direction
        'flex-row'         => 'flex-direction: row;',
        'flex-row-reverse' => 'flex-direction: row-reverse;',
        'flex-col'         => 'flex-direction: column;',
        'flex-col-reverse' => 'flex-direction: column-reverse;',

        // Flex Wrap
        'flex-wrap'         => 'flex-wrap: wrap;',
        'flex-wrap-reverse' => 'flex-wrap: wrap-reverse;',
        'flex-nowrap'       => 'flex-wrap: nowrap;',

        // Align Items (Eixo Transversal)
        'items-start'    => 'align-items: flex-start;',
        'items-end'      => 'align-items: flex-end;',
        'items-center'   => 'align-items: center;',
        'items-baseline' => 'align-items: baseline;',
        'items-stretch'  => 'align-items: stretch;',

        // Justify Content (Eixo Principal)
        'justify-start'   => 'justify-content: flex-start;',
        'justify-end'     => 'justify-content: flex-end;',
        'justify-center'  => 'justify-content: center;',
        'justify-between' => 'justify-content: space-between;',
        'justify-around'  => 'justify-content: space-around;',
        'justify-evenly'  => 'justify-content: space-evenly;',
        'justify-stretch' => 'justify-content: stretch;',

        // Align Content
        'content-center'  => 'align-content: center;',
        'content-start'   => 'align-content: flex-start;',
        'content-end'     => 'align-content: flex-end;',
        'content-between' => 'align-content: space-between;',
        'content-around'  => 'align-content: space-around;',
        'content-evenly'  => 'align-content: space-evenly;',

        // Align Self
        'self-auto'     => 'align-self: auto;',
        'self-start'    => 'align-self: flex-start;',
        'self-end'      => 'align-self: flex-end;',
        'self-center'   => 'align-self: center;',
        'self-stretch'  => 'align-self: stretch;',
        'self-baseline' => 'align-self: baseline;',

        // Flex Grow / Shrink
        'grow'          => 'flex-grow: 1;',
        'grow-0'        => 'flex-grow: 0;',
        'shrink'        => 'flex-shrink: 1;',
        'shrink-0'      => 'flex-shrink: 0;',

        // Flex Presets
        'flex-1'        => 'flex: 1 1 0%;',
        'flex-auto'     => 'flex: 1 1 auto;',
        'flex-initial'  => 'flex: 0 1 auto;',
        'flex-none'     => 'flex: none;',
    ];

    /**
     * Verifica se o candidato de classe é suportado por esta regra
     */
    public function supports(UtilityCandidate $candidate): bool
    {
        $raw = $candidate->rawClass;
        $prefix = $candidate->utilityPrefix;

        // 1. Mapeamento Estático
        if (isset($this->staticMap[$raw])) {
            return true;
        }

        // 2. Mapeamento Dinâmico (gap-*, gap-x-*, gap-y-*, grid-cols-*, flex-*)
        return in_array($prefix, ['gap', 'gap-x', 'gap-y', 'grid-cols', 'flex'], true);
    }

    /**
     * Gera as declarações CSS puras
     */
    public function generateCss(UtilityCandidate $candidate): string
    {
        $raw = $candidate->rawClass;

        // 1. Caso Estático Simples
        if (isset($this->staticMap[$raw])) {
            return $this->staticMap[$raw];
        }

        // 2. Caso Dinâmico: Gap (Gap, Gap-X, Gap-Y)
        if (str_starts_with($candidate->utilityPrefix, 'gap')) {
            return $this->generateGapCss($candidate);
        }

        // 3. Caso Dinâmico: Grid Columns (ex: grid-cols-12, grid-cols-[1fr_2fr])
        if ($candidate->utilityPrefix === 'grid-cols' || str_starts_with($raw, 'grid-cols-')) {
            return $this->generateGridColsCss($candidate);
        }

        return '';
    }

    /**
     * Processa regras de Gap (espaçamento interno de flexbox/grid)
     */
    private function generateGapCss(UtilityCandidate $candidate): string
    {
        // Resolve valor arbitrário [20px] ou valor da escala
        $value = $candidate->isArbitrary
            ? trim($candidate->value, '[]')
            : ($this->spacingScale[$candidate->value] ?? null);

        if (!$value) {
            return '';
        }

        // Se a classe for gap-x-* ou gap-y-*
        if (str_starts_with($candidate->rawClass, 'gap-x-')) {
            return "column-gap: {$value};";
        }

        if (str_starts_with($candidate->rawClass, 'gap-y-')) {
            return "row-gap: {$value};";
        }

        return "gap: {$value};";
    }

    /**
     * Processa regras de Grid Template Columns
     */
    private function generateGridColsCss(UtilityCandidate $candidate): string
    {
        if ($candidate->isArbitrary) {
            // Suporta grid-cols-[200px_minmax(0,1fr)] -> substitui _ por espaço no CSS
            $val = str_replace('_', ' ', trim($candidate->value, '[]'));
            return "grid-template-columns: {$val};";
        }

        if ($candidate->value === 'none') {
            return 'grid-template-columns: none;';
        }

        if (is_numeric($candidate->value)) {
            return "grid-template-columns: repeat({$candidate->value}, minmax(0, 1fr));";
        }

        return '';
    }
}
