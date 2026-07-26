<?php

namespace Example\Assets;

class AssetsCollector
{
    private array $styles = [];
    private array $scripts = [];

    public function addStyle(string $filePath): void
    {
        if (!isset($this->styles[$filePath]) && file_exists($filePath)) {
            $this->styles[] = file_get_contents($filePath);
        }
    }

    public function addScript(string $filePath): void
    {
        if (!isset($this->styles[$filePath]) && file_exists($filePath)) {
            $this->styles[] = file_get_contents($filePath);
        }
    }

    public function renderStyles(): string
    {
        if (empty($this->styles)) {
            return '';
        }

        $cssContent = implode("\n", $this->styles);
        return "<style type=\"text/css\">\n{$cssContent}</style>";
    }

    public function renderScripts(): string
    {
        if (empty($this->scripts)) {
            return '';
        }

        $jsContent = implode("\n", $this->scripts);
        return "<script type=\"text/javascript\">\n{$jsContent}\n</script>";
    }

    public function injectIntoHtml(string $html): string
    {
        $styleTag = $this->renderStyles();
        $scriptTag = $this->renderScripts();

        if (!empty($styleTag)) {
            if (str_contains($html, '</head>')) {
                $html = str_replace('</head>', "{$styleTag}\n</head>", $html);
            } else {
                $html = $styleTag . $html;
            }
        }

        if (!empty($scriptTag)) {
            if (str_contains($html, '</body>')) {
                $html = str_replace('</body>', "{$scriptTag}\n</body>", $html);
            } else {
                $html .= $scriptTag;
            }
        }

        return $html;
    }
}
