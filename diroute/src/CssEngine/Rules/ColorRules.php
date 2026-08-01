<?php

namespace Diroute\CssEngine\Rules;

use Diroute\CssEngine\Contract\RuleInterface;
use Diroute\CssEngine\Parser\UtilityCandidate;

class ColorRules implements RuleInterface
{
    /**
     * Paleta de cores padrão do Tailwind CSS
     */
    private array $colors = [
        'transparent' => 'transparent',
        'current'     => 'currentColor',
        'black'       => '#000000',
        'white'       => '#ffffff',

        // Slate
        'slate-50'  => '#f8fafc',
        'slate-100' => '#f1f5f9',
        'slate-200' => '#e2e8f0',
        'slate-300' => '#cbd5e1',
        'slate-400' => '#94a3b8',
        'slate-500' => '#64748b',
        'slate-600' => '#475569',
        'slate-700' => '#334155',
        'slate-800' => '#1e293b',
        'slate-900' => '#0f172a',
        'slate-950' => '#020617',

        // Gray
        'gray-50'  => '#f9fafb',
        'gray-100' => '#f3f4f6',
        'gray-200' => '#e5e7eb',
        'gray-300' => '#d1d5db',
        'gray-400' => '#9ca3af',
        'gray-500' => '#6b7280',
        'gray-600' => '#4b5563',
        'gray-700' => '#374151',
        'gray-800' => '#1f2937',
        'gray-900' => '#111827',
        'gray-950' => '#030712',

        // Red
        'red-50'  => '#fef2f2',
        'red-100' => '#fee2e2',
        'red-200' => '#fecaca',
        'red-300' => '#fca5a5',
        'red-400' => '#f87171',
        'red-500' => '#ef4444',
        'red-600' => '#dc2626',
        'red-700' => '#b91c1c',
        'red-800' => '#991b1b',
        'red-900' => '#7f1d1d',
        'red-950' => '#450a0a',

        // Orange
        'orange-50'  => '#fff7ed',
        'orange-100' => '#ffedd5',
        'orange-200' => '#fed7aa',
        'orange-300' => '#fdba74',
        'orange-400' => '#fb923c',
        'orange-500' => '#f97316',
        'orange-600' => '#ea580c',
        'orange-700' => '#c2410c',
        'orange-800' => '#9a3412',
        'orange-900' => '#7c2d12',
        'orange-950' => '#431407',

        // Yellow
        'yellow-50'  => '#fefce8',
        'yellow-100' => '#fef9c3',
        'yellow-200' => '#fef08a',
        'yellow-300' => '#fde047',
        'yellow-400' => '#facc15',
        'yellow-500' => '#eab308',
        'yellow-600' => '#ca8a04',
        'yellow-700' => '#a16207',
        'yellow-800' => '#854d0e',
        'yellow-900' => '#713f12',
        'yellow-950' => '#422006',

        // Green
        'green-50'  => '#f0fdf4',
        'green-100' => '#dcfce7',
        'green-200' => '#bbf7d0',
        'green-300' => '#86efac',
        'green-400' => '#4ade80',
        'green-500' => '#22c55e',
        'green-600' => '#16a34a',
        'green-700' => '#15803d',
        'green-800' => '#166534',
        'green-900' => '#14532d',
        'green-950' => '#052e16',

        // Emerald
        'emerald-50'  => '#ecfdf5',
        'emerald-100' => '#d1fae5',
        'emerald-200' => '#a7f3d0',
        'emerald-300' => '#6ee7b7',
        'emerald-400' => '#34d399',
        'emerald-500' => '#10b981',
        'emerald-600' => '#059669',
        'emerald-700' => '#047857',
        'emerald-800' => '#065f46',
        'emerald-900' => '#064e3b',
        'emerald-950' => '#022c22',

        // Blue
        'blue-50'  => '#eff6ff',
        'blue-100' => '#dbeafe',
        'blue-200' => '#bfdbfe',
        'blue-300' => '#93c5fd',
        'blue-400' => '#60a5fa',
        'blue-500' => '#3b82f6',
        'blue-600' => '#2563eb',
        'blue-700' => '#1d4ed8',
        'blue-800' => '#1e40af',
        'blue-900' => '#1e3a8a',
        'blue-950' => '#172554',

        // Indigo
        'indigo-50'  => '#eef2ff',
        'indigo-100' => '#e0e7ff',
        'indigo-200' => '#c7d2fe',
        'indigo-300' => '#a5b4fc',
        'indigo-400' => '#818cf8',
        'indigo-500' => '#6366f1',
        'indigo-600' => '#4f46e5',
        'indigo-700' => '#4338ca',
        'indigo-800' => '#3730a3',
        'indigo-900' => '#312e81',
        'indigo-950' => '#1e1b4b',

        // Purple
        'purple-50'  => '#faf5ff',
        'purple-100' => '#f3e8ff',
        'purple-200' => '#e9d5ff',
        'purple-300' => '#d8b4fe',
        'purple-400' => '#c084fc',
        'purple-500' => '#a855f7',
        'purple-600' => '#9333ea',
        'purple-700' => '#7e22ce',
        'purple-800' => '#6b21a8',
        'purple-900' => '#581c87',
        'purple-950' => '#3b0764',

        // Pink
        'pink-50'  => '#fdf2f8',
        'pink-100' => '#fce7f3',
        'pink-200' => '#fbcfe8',
        'pink-300' => '#f9a8d4',
        'pink-400' => '#f472b6',
        'pink-500' => '#ec4899',
        'pink-600' => '#db2777',
        'pink-700' => '#be185d',
        'pink-800' => '#9d174d',
        'pink-900' => '#831843',
        'pink-950' => '#500724',
    ];

    /**
     * Define os prefixos suportados por esta regra
     */
    public function supports(UtilityCandidate $candidate): bool
    {
        return in_array($candidate->utilityPrefix, ['bg', 'text', 'border'], true);
    }

    /**
     * Gera a declaração CSS correspondente
     */
    public function generateCss(UtilityCandidate $candidate): string
    {
        // 1. Resolve o valor final da cor (seja escala ou arbitrário)
        $colorValue = $this->resolveColor($candidate);

        if (!$colorValue) {
            return '';
        }

        // 2. Mapeia para a propriedade CSS adequada
        return match ($candidate->utilityPrefix) {
            'bg'     => "background-color: {$colorValue};",
            'text'   => "color: {$colorValue};",
            'border' => "border-color: {$colorValue};",
            default  => ''
        };
    }

    /**
     * Resolve se a cor vem da paleta estática ou de um valor arbitrário [color]
     */
    private function resolveColor(UtilityCandidate $candidate): ?string
    {
        // Tratamento para valores arbitrários (ex: bg-[#121212] ou text-[rgb(0,0,0)])
        if ($candidate->isArbitrary) {
            $rawVal = trim($candidate->value, '[]');
            // Substitui underlines por espaços para suportar RGB e HSL (ex: [rgb(0_0_0)] -> rgb(0 0 0))
            return str_replace('_', ' ', $rawVal);
        }

        // Busca o nome da cor na tabela (ex: $candidate->value é "red-500" ou "white")
        return $this->colors[$candidate->value] ?? null;
    }
}
