<?php

namespace Diroute\Http\Router;

use Diroute\Http\Attribute\Page;

readonly class Route
{
    /**
     * @param string $uriPattern Ex: "/" ou "/blog/{id}"
     * @param string $regexPattern Regex compilada Ex: "#^/blog/(?P<id>[^/]+)$#u"
     * @param string $controllerClass FQCN da classe
     * @param Page $pageAttribute Atributo #[Page] extraído
     * @param string $pageDirectory Caminho absoluto da pasta onde residem Page.php e o template
     */
    public function __construct(
        public string $uriPattern,
        public string $regexPattern,
        public string $controllerClass,
        public Page $pageAttribute,
        public string $pageDirectory
    ) {}
}
