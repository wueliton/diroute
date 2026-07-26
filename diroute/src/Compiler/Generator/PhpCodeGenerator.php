<?php

namespace Diroute\Compiler\Generator;

use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Parser\AST;
use Diroute\Compiler\Parser\Node\ComponentNode;
use Diroute\Compiler\Parser\Node\DirectiveNode;
use Diroute\Compiler\Parser\Node\ElementNode;
use Diroute\Compiler\Parser\Node\RootNode;
use Diroute\Compiler\Parser\Node\TextNode;
use Diroute\Compiler\Contract\NodeEmitterInterface;
use Diroute\Compiler\Generator\Emitter\ComponentEmitter;
use Diroute\Compiler\Generator\Emitter\DirectiveEmitter;
use Diroute\Compiler\Generator\Emitter\ElementEmitter;
use Diroute\Compiler\Generator\Emitter\TextEmitter;

class PhpCodeGenerator
{
    /** @var array<string, NodeEmitterInterface> */
    private array $emitters = [];

    public function __construct()
    {
        $this->registerDefaultEmitters();
    }

    public function registerEmitter(string $nodeClass, NodeEmitterInterface $emitter): self
    {
        $this->emitters[$nodeClass] = $emitter;
        return $this;
    }

    public function generate(AST $ast): string
    {
        $buffer = new PHPBuffer();
        $this->traverse($ast->root, $buffer);
        $content = $buffer->getContents();
        return $content;
    }

    private function traverse(NodeInterface $node, PHPBuffer $buffer): void
    {
        if ($node instanceof RootNode) {
            foreach ($node->getChildren() as $child) {
                $this->traverse($child, $buffer);
            }
            return;
        }

        $nodeClass = $node::class;

        if (isset($this->emitters[$nodeClass])) {
            $this->emitters[$nodeClass]->emit(
                $node,
                $buffer,
                fn(NodeInterface $child) => $this->traverse($child, $buffer)
            );
        }
    }

    private function registerDefaultEmitters(): void
    {
        $this->registerEmitter(TextNode::class, new TextEmitter());
        $this->registerEmitter(ElementNode::class, new ElementEmitter());
        $this->registerEmitter(DirectiveNode::class, new DirectiveEmitter());
        $this->registerEmitter(ComponentNode::class, new ComponentEmitter());
    }
}
