<?php

namespace Diroute\CssEngine\Rules;

use Diroute\CssEngine\Contract\RuleInterface;
use Diroute\CssEngine\Parser\UtilityCandidate;

class SizeRules implements RuleInterface
{
    /**
     * Tabela de escala de tamanhos padrão do Tailwind (rem / px)
     */
    private array $sizeScale = [
        '0'     => '0px',
        'px'    => '1px',
        '0.5'   => '0.125rem',
        '1'     => '0.25rem',
        '1.5'   => '0.375rem',
        '2'     => '0.5rem',
        '2.5'   => '0.625rem',
        '3'     => '0.75rem',
        '3.5'   => '0.875rem',
        '4'     => '1rem',
        '5'     => '1.25rem',
        '6'     => '1.5rem',
        '7'     => '1.75rem',
        '8'     => '2rem',
        '9'     => '2.25rem',
        '10'    => '2.5rem',
        '11'    => '2.75rem',
        '12'    => '3rem',
        '14'    => '3.5rem',
        '16'    => '4rem',
        '20'    => '5rem',
        '24'    => '6rem',
        '28'    => '7rem',
        '32'    => '8rem',
        '36'    => '9rem',
        '40'    => '10rem',
        '44'    => '11rem',
        '48'    => '12rem',
        '52'    => '13rem',
        '56'    => '14rem',
        '60'    => '15rem',
        '64'    => '16rem',
        '72'    => '18rem',
        '80'    => '20rem',
        '96'    => '24rem',
    ];

    /**
     * Frações comuns para larguras/alturas em porcentagem
     */
    private array $fractions = [
        '1/2' => '50%',
        '1/3' => '33.333333%',
        '2/3' => '66.666667%',
        '1/4' => '25%',
        '2/4' => '50%',
        '3/4' => '75%',
        '1/5' => '20%',
        '2/5' => '40%',
        '3/5' => '60%',
        '4/5' => '80%',
        '1/6' => '16.666667%',
        '5/6' => '83.333333%',
        '1/12' => '8.333333%',
        '5/12' => '41.666667%',
        '7/12' => '58.333333%',
        '11/12' => '91.666667%',
        'full' => '100%',
    ];

    /**
     * Palavras-chave especiais e tamanhos de breakpoint para max-w
     */
    private array $maxWidthBreakpoints = [
        'xs'   => '20rem',
        'sm'   => '24rem',
        'md'   => '28rem',
        'lg'   => '32rem',
        'xl'   => '36rem',
        '2xl'  => '42rem',
        '3xl'  => '48rem',
        '4xl'  => '56rem',
        '5xl'  => '64rem',
        '6xl'  => '72rem',
        '7xl'  => '80rem',
        'prose' => '65ch',
    ];

    /**
     * Verifica se a classe candidato pertence a regras de dimensão
     */
    public function supports(UtilityCandidate $candidate): bool
    {
        $prefix = $candidate->utilityPrefix;

        return in_array($prefix, [
            'w',
            'h',
            'min-w',
            'max-w',
            'min-h',
            'max-h'
        ], true);
    }

    /**
     * Gera as declarações CSS correspondentes
     */
    public function generateCss(UtilityCandidate $candidate): string
    {
        $value = $this->resolveSizeValue($candidate);

        if ($value === null) {
            return '';
        }

        return match ($candidate->utilityPrefix) {
            'w'     => "width: {$value};",
            'h'     => "height: {$value};",
            'min-w' => "min-width: {$value};",
            'max-w' => "max-width: {$value};",
            'min-h' => "min-height: {$value};",
            'max-h' => "max-height: {$value};",
            default => ''
        };
    }

    /**
     * Resolve o valor CSS final (escala, porcentagem, palavras-chave ou valor arbitrário)
     */
    private function resolveSizeValue(UtilityCandidate $candidate): ?string
    {
        // 1. Suporte a Valores Arbitrários: w-[350px], h-[50vh], max-w-[1200px]
        if ($candidate->isArbitrary) {
            $val = trim($candidate->value, '[]');
            return str_replace('_', ' ', $val);
        }

        $val = $candidate->value;

        // 2. Palavras-chave Especiais
        if ($val === 'auto') return 'auto';
        if ($val === 'full') return '100%';
        if ($val === 'min')  return 'min-content';
        if ($val === 'max')  return 'max-content';
        if ($val === 'fit')  return 'fit-content';

        // Viewport Units
        if ($val === 'screen') {
            return str_starts_with($candidate->utilityPrefix, 'h') || str_starts_with($candidate->utilityPrefix, 'min-h') || str_starts_with($candidate->utilityPrefix, 'max-h')
                ? '100vh'
                : '100vw';
        }

        // Viewport Dynamic (svh, lvh, dvh / svw, lvw, dvw)
        if ($val === 'svh') return '100svh';
        if ($val === 'dvh') return '100dvh';
        if ($val === 'lvh') return '100lvh';

        // 3. Frações (ex: w-1/2, h-3/4)
        if (isset($this->fractions[$val])) {
            return $this->fractions[$val];
        }

        // 4. Breakpoints especiais de Max-Width (ex: max-w-md, max-w-7xl)
        if ($candidate->utilityPrefix === 'max-w' && isset($this->maxWidthBreakpoints[$val])) {
            return $this->maxWidthBreakpoints[$val];
        }

        // 5. Escala Numérica Padrão (ex: w-4, h-64)
        return $this->sizeScale[$val] ?? null;
    }
}
