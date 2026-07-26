<?php

namespace Diroute\Profiler;

class Profiler
{
    private array $timers = [];
    private array $records = [];

    /**
     * Inicia a medição de um bloco/etapa
     */
    public function start(string $name): void
    {
        $this->timers[$name] = microtime(true);
    }

    /**
     * Para a medição e registra a duração em milissegundos (ms)
     */
    public function stop(string $name): float
    {
        if (!isset($this->timers[$name])) {
            return 0.0;
        }

        $duration = (microtime(true) - $this->timers[$name]) * 1000; // Em ms
        $this->records[$name] = ($this->records[$name] ?? 0.0) + $duration;

        unset($this->timers[$name]);

        return $duration;
    }

    /**
     * Executa um callback registrando automaticamente o tempo de execução
     */
    public function profile(string $name, callable $callback): mixed
    {
        $this->start($name);
        try {
            return $callback();
        } finally {
            $this->stop($name);
        }
    }

    /**
     * Retorna o relatório formatado de todas as etapas medidas
     */
    public function getReport(): array
    {
        $total = array_sum($this->records);
        $report = [];

        foreach ($this->records as $step => $timeMs) {
            $percentage = $total > 0 ? ($timeMs / $total) * 100 : 0;
            $report[$step] = [
                'time_ms' => round($timeMs, 4),
                'percentage' => round($percentage, 2) . '%'
            ];
        }

        $report['TOTAL'] = [
            'time_ms' => round($total, 4),
            'percentage' => '100%'
        ];

        return $report;
    }

    /**
     * Exibe o relatório em formato HTML para debug (ou log)
     */
    public function renderHtmlSummary(): string
    {
        $report = $this->getReport();
        $html = "<!-- Diroute Profiler Summary -->\n";
        $html .= "<style>
            .diroute-profiler { position: fixed; bottom: 10px; right: 10px; background: #1e1e1e; color: #4af626; font-family: monospace; font-size: 12px; padding: 12px; border-radius: 6px; z-index: 999999; box-shadow: 0 4px 12px rgba(0,0,0,0.5); }
            .diroute-profiler table { border-collapse: collapse; width: 100%; }
            .diroute-profiler td, .diroute-profiler th { padding: 3px 8px; text-align: left; }
            .diroute-profiler th { border-bottom: 1px solid #4af626; color: #fff; }
        </style>\n";
        $html .= "<div class='diroute-profiler'>\n";
        $html .= "<strong>⚡ Diroute Pipeline Profiler</strong><hr style='border-color:#333'>";
        $html .= "<table><thead><tr><th>Etapa</th><th>Tempo (ms)</th><th>%</th></tr></thead><tbody>";

        foreach ($report as $step => $data) {
            $style = $step === 'TOTAL' ? "font-weight:bold; color:#fff; border-top:1px solid #444;" : "";
            $html .= "<tr style='{$style}'><td>{$step}</td><td>{$data['time_ms']} ms</td><td>{$data['percentage']}</td></tr>";
        }

        $html .= "</tbody></table></div>";

        return $html;
    }
}
