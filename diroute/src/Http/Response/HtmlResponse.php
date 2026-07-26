<?php

namespace Diroute\Http\Response;

class HtmlResponse
{
    /**
     * @param string $htmlConteudo HTML final renderizado pelo SSRPageRenderer
     * @param int $statusCode Código HTTP (padrão 200 OK)
     * @param array<string, string> $headers Cabeçalhos adicionais
     * @param int $revalidate Tempo de revalidação ISR em segundos (extraído de #[Page])
     */
    public function __construct(
        private readonly string $htmlConteudo,
        private readonly int $statusCode = 200,
        private array $headers = [],
        private readonly int $revalidate = 0
    ) {
        $this->applyDefaultHeaders();
        $this->applyCacheControl();
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->htmlConteudo;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function withHeader(string $name, string $value): self
    {
        $new = clone $this;
        $new->headers[$name] = $value;
        return $new;
    }

    /**
     * Envia os cabeçalhos HTTP e o corpo para a saída do servidor
     */
    public function send(): void
    {
        if (!\headers_sent()) {
            \http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                \header("{$name}: {$value}", true);
            }
        }

        echo $this->htmlConteudo;
    }

    private function applyDefaultHeaders(): void
    {
        $this->headers['Content-Type'] ??= 'text/html; charset=utf-8';
        $this->headers['X-Powered-By'] ??= 'Diroute Framework';
    }

    private function applyCacheControl(): void
    {
        if (isset($this->headers['Cache-Control'])) {
            return;
        }

        if ($this->revalidate > 0) {
            // Estratégia ISR (Incremental Static Regeneration)
            $this->headers['Cache-Control'] = sprintf(
                'public, max-age=%d, s-maxage=%d, stale-while-revalidate',
                $this->revalidate,
                $this->revalidate
            );
        } else {
            // Renderização puramente dinâmica (sem cache de CDN/Browser)
            $this->headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
        }
    }
}
