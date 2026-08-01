<?php

namespace Diroute\CssEngine;

class CssCollector
{
    private static ?self $instance = null;
    /** @var array<string, bool> Usa chaves para garantir unicidade O(1) */
    private array $classes = [];

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Adiciona uma ou várias classes ao acumulador da página
     */
    public static function add(string ...$classNames): void
    {
        $collector = self::getInstance();

        foreach ($classNames as $className) {
            $className = trim($className);
            if ($className === '') {
                continue;
            }

            // Se a string contiver múltiplas classes (ex: "flex items-center p-4")
            if (str_contains($className, ' ')) {
                $tok = strtok($className, " \n\t\r");
                while ($tok !== false) {
                    $collector->classes[$tok] = true;
                    $tok = strtok(" \n\t\r");
                }
            } else {
                $collector->classes[$className] = true;
            }
        }
    }

    /**
     * Retorna a lista de classes únicas coletadas até o momento
     * @return string[]
     */
    public static function getUniqueClasses(): array
    {
        return array_keys(self::getInstance()->classes);
    }

    /**
     * Limpa o acumulador para a próxima página
     */
    public static function flush(): void
    {
        self::getInstance()->classes = [];
    }
}
