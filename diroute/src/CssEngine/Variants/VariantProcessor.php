<?php

namespace Diroute\CssEngine\Variants;

use Diroute\CssEngine\Parser\UtilityCandidate;

class VariantProcessor
{
    private array $mediaQueries = [
        'sm' => '@media (min-width: 640px)',
        'md' => '@media (min-width: 768px)',
        'lg' => '@media (min-width: 1024px)',
    ];

    private array $pseudoClasses = [
        'hover' => ':hover',
        'focus' => ':focus',
        'active' => ':active',
    ];

    public function wrapCss(UtilityCandidate $candidate, string $innerDeclarations): string
    {
        // 1. Escapa a classe para uso no CSS (ex: hover:bg-red-500 -> .hover\:bg-red-500)
        $escapedClassName = '.' . str_replace([':', '[', ']', '/'], ['\\:', '\\[', '\\]', '\\/'], $candidate->rawClass);

        $pseudo = '';
        $mediaQuery = null;

        foreach ($candidate->variants as $variant) {
            if (isset($this->pseudoClasses[$variant])) {
                $pseudo .= $this->pseudoClasses[$variant];
            } elseif (isset($this->mediaQueries[$variant])) {
                $mediaQuery = $this->mediaQueries[$variant];
            }
        }

        $cssRule = "{$escapedClassName}{$pseudo} { {$innerDeclarations} }";

        // Se houver Media Query (ex: md:), envelopa o bloco
        if ($mediaQuery) {
            return "{$mediaQuery} { {$cssRule} }";
        }

        return $cssRule;
    }
}
