<?php

namespace Diroute\Compiler\Parser\Registry;

readonly class DirectiveConfig
{
    /**
     * @param string $name Nome da diretiva sem o '@' (ex: "if", "for")
     * @param bool $hasArguments Se a diretiva espera/requer parênteses com argumentos
     * @param string[] $allowedConnections Nomes de diretivas que podem ser encadeadas nesta (ex: ["elseif", "else"] para "if")
     * @param string $rendererClass Classe FQCN que implementa DirectiveRendererInterface
     */
    public function __construct(
        public string $name,
        public bool $hasArguments,
        public array $allowedConnections,
        public string $rendererClass
    ) {}
}
