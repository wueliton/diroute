<?php

namespace Diroute\Http\Router;

use Diroute\Http\Attribute\Page;

readonly class RouteMatch
{
    /**
     * @param string $controllerClass FQCN da classe decorada com #[Page]
     * @param Page $pageAttribute Atributo #[Page] com metadados (title, template, revalidate)
     * @param string $filePath Caminho do arquivo .php da página
     * @param array<string, string> $params Parâmetros dinâmicos extraídos do path (ex: ['id' => '10'])
     */
    public function __construct(
        public string $controllerClass,
        public Page $pageAttribute,
        public string $filePath,
        public array $params = []
    ) {}
}
