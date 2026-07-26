<?php

namespace Diroute\Http\Router;

use Diroute\Http\Attribute\Page;

class RouteNode
{
    /** @var array<string, RouteNode> Filhos estáticos (ex: 'users' => RouteNode) */
    public array $staticChildren = [];

    /** @var RouteNode|null Filho dinâmico (ex: {id} ou [id]) */
    public ?RouteNode $dynamicChild = null;

    /** @var string|null Nome do parâmetro dinâmico capturado (ex: 'id') */
    public ?string $paramName = null;

    /** @var string|null FQCN da classe do controller */
    public ?string $controllerClass = null;

    /** @var Page|null Instância do atributo #[Page] */
    public ?Page $pageAttribute = null;

    /** @var string|null Caminho absoluto do arquivo físico da página */
    public ?string $filePath = null;

    /** @var int Timestamp de última modificação do arquivo físico */
    public int $mtime = 0;

    public function isEndpoint(): bool
    {
        return $this->controllerClass !== null;
    }
}
