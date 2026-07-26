<?php

use Diroute\Compiler\Optimizer\OptimizerPipeline;
use Diroute\Compiler\Optimizer\Pass\DeadCodeEliminationPass;
use Diroute\Compiler\Optimizer\Pass\StaticTextMergerPass;
use Diroute\Compiler\Parser\AST;
use Diroute\Compiler\Parser\Node\DirectiveNode;
use Diroute\Compiler\Parser\Node\RootNode;
use Diroute\Compiler\Parser\Node\TextNode;
use PHPUnit\Framework\TestCase;

class OptimizerTest extends TestCase
{
    public function testStaticTextMergerPassCombinesAdjacentTextNodes(): void
    {
        $root = new RootNode([
            new TextNode('<h1>', true),
            new TextNode('Título', true),
            new TextNode('</h1>', true),
        ]);

        $ast = new AST($root);
        $pipeline = new OptimizerPipeline([new StaticTextMergerPass()]);

        $optimizedAst = $pipeline->process($ast);
        $children = $optimizedAst->root->getChildren();

        $this->assertCount(1, $children);
        $this->assertSame('<h1>Título</h1>', $children[0]->content);
    }

    public function testDeadCodeEliminationPassRemovesFalseIf(): void
    {
        $root = new RootNode([
            new TextNode('<div>', true),
            new DirectiveNode('if', 'false', 'DummyRenderer', [
                new TextNode('Conteúdo Invisível', true)
            ]),
            new TextNode('</div>', true),
        ]);

        $ast = new AST($root);
        $pipeline = new OptimizerPipeline([
            new DeadCodeEliminationPass(),
            new StaticTextMergerPass()
        ]);

        $optimizedAst = $pipeline->process($ast);
        $children = $optimizedAst->root->getChildren();

        $this->assertCount(1, $children);
        $this->assertSame('<div></div>', $children[0]->content);
    }
}
