<?php

namespace Diroute\Http\Context;

use ReflectionClass;
use ReflectionMethod;

class ContextHydrator
{
    /**
     * Extrai propriedades e métodos de uma instância anotada com #[Page] ou #[Component]
     *
     * @param object $instance Instância do Controller/Page ou Componente
     * @param array<string, mixed> $initialData Dados/props iniciais recebidos (ex: parâmetros de rota)
     * @return array<string, mixed> Tabela de símbolos pronta para o TemplateRunner
     */
    public function hydrate(object $instance, array $initialData = []): array
    {
        $context = $initialData;
        $reflection = new ReflectionClass($instance);

        // 1. Injeta $this da própria instância para acessos diretos no template ($this->method())
        $context['this'] = $instance;

        // 2. Extrai todas as PROPRIEDADES declaradas na classe (públicas, promovidas do construtor, etc.)
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            // Só injeta se a propriedade estiver inicializada
            if ($property->isInitialized($instance)) {
                $context[$property->getName()] = $property->getValue($instance);
            }
        }

        // 3. Extrai o retorno do método __invoke() se ele existir (padrão de Controller)
        if ($reflection->hasMethod('__invoke')) {
            $invokeMethod = $reflection->getMethod('__invoke');
            if ($invokeMethod->isPublic()) {
                // Se a ação retornar um array de variáveis, funde com o contexto
                $invokeData = $instance();
                if (\is_array($invokeData)) {
                    $context = \array_merge($context, $invokeData);
                }
            }
        }

        // 4. Mapeia MÉTODOS PÚBLICOS da classe como funções de primeira classe (Closures)
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            // Ignora métodos mágicos e construtores
            if (\str_starts_with($methodName, '__')) {
                continue;
            }

            // Disponibiliza o método como uma variável invocável: $myFunction(...)
            $context[$methodName] = $method->getClosure($instance);
        }

        return $context;
    }

    public function hydrateComponent(object $instance, array $passedProps = []): array
    {
        $context = [];
        $reflection = new ReflectionClass($instance);

        // 1. Extrai todas as PROPRIEDADES declaradas na classe (valores default ou do construtor)
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            if ($property->isInitialized($instance)) {
                $context[$property->getName()] = $property->getValue($instance);
            }
        }

        // 2. Sobrescreve com as PROPS PASSADAS NA TAG (<app-button type="submit">)
        foreach ($passedProps as $key => $value) {
            $context[$key] = $value;
        }

        // 3. Mapeia MÉTODOS PÚBLICOS da classe como Closures invocáveis (ex: {{ $getCssClass() }})
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();
            if (!\str_starts_with($methodName, '__')) {
                $context[$methodName] = $method->getClosure($instance);
            }
        }

        return $context;
    }
}
