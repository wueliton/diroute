<?php

namespace Tests\Unit\Parser;

use Diroute\Compiler\Lexer\Lexer;
use Diroute\Compiler\Parser\AST;
use Diroute\Compiler\Parser\Node\DirectiveNode;
use Diroute\Compiler\Parser\Node\TextNode;
use Diroute\Compiler\Parser\Parser;
use Diroute\Compiler\Parser\Registry\DirectiveConfig;
use Diroute\Compiler\Parser\Registry\DirectiveRegistry;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ParserTest extends TestCase
{
    private Lexer $lexer;
    private DirectiveRegistry $registry;
    private Parser $parser;

    protected function setUp(): void
    {
        $this->lexer = new Lexer();
        $this->registry = new DirectiveRegistry();
        $this->parser = new Parser($this->registry);
    }

    public function testParseSimpleTextAndInterpolation(): void
    {
        $tokens = $this->lexer->tokenize('<h1>Olá {{ $name }}</h1>');
        $ast = $this->parser->parse($tokens);

        $this->assertInstanceOf(AST::class, $ast);
        $children = $ast->root->getChildren();

        $this->assertCount(3, $children);

        $this->assertInstanceOf(TextNode::class, $children[0]);
        $this->assertSame('<h1>Olá ', $children[0]->content);
        $this->assertTrue($children[0]->isRawHtml);

        $this->assertInstanceOf(TextNode::class, $children[1]);
        $this->assertSame('$name', $children[1]->content);
        $this->assertFalse($children[1]->isRawHtml);

        $this->assertInstanceOf(TextNode::class, $children[2]);
        $this->assertSame('</h1>', $children[2]->content);
    }

    public function testParseUnregisteredDirectiveFallbackToText(): void
    {
        $tokens = $this->lexer->tokenize('Siga-nos no @twitter agora');
        $ast = $this->parser->parse($tokens);

        $children = $ast->root->getChildren();

        $this->assertInstanceOf(TextNode::class, $children[1]);
        $this->assertSame('@twitter', $children[1]->content);
    }

    public function testParseIfElseifElseBranching(): void
    {
        $template = '@if($role === "admin") { <a href="/admin">Admin</a> } @elseif($role === "editor") { <a href="/editor">Editor</a> } @else { <p>Guest</p> }';
        $tokens = $this->lexer->tokenize($template);
        $ast = $this->parser->parse($tokens);

        $children = $ast->root->getChildren();
        $this->assertCount(1, $children);

        /** @var DirectiveNode $ifNode */
        $ifNode = $children[0];
        $this->assertInstanceOf(DirectiveNode::class, $ifNode);
        $this->assertSame('if', $ifNode->name);
        $this->assertSame('$role === "admin"', $ifNode->expression);

        // Valida as duas branches: @elseif e @else
        $this->assertCount(2, $ifNode->branches);
        $this->assertSame('elseif', $ifNode->branches[0]->name);
        $this->assertSame('$role === "editor"', $ifNode->branches[0]->expression);

        $this->assertSame('else', $ifNode->branches[1]->name);
        $this->assertNull($ifNode->branches[1]->expression);
    }

    public function testParseForWithEmptyBranch(): void
    {
        $template = '@for($users as $user) { <li>{{ $user->name }}</li> } @empty { <p>Nenhum usuário</p> }';
        $tokens = $this->lexer->tokenize($template);
        $ast = $this->parser->parse($tokens);

        $children = $ast->root->getChildren();
        $this->assertCount(1, $children);

        /** @var DirectiveNode $forNode */
        $forNode = $children[0];

        $this->assertInstanceOf(DirectiveNode::class, $forNode);
        $this->assertSame('for', $forNode->name);
        $this->assertSame('$users as $user', $forNode->expression);

        $this->assertCount(1, $forNode->branches);
        $this->assertSame('empty', $forNode->branches[0]->name);
    }

    public function testThrowsExceptionWhenDirectiveMissingRequiredArguments(): void
    {
        $tokens = $this->lexer->tokenize('@if { <p>Erro</p> }');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("A diretiva '@if' exige argumentos entre parênteses");

        $this->parser->parse($tokens);
    }

    public function testRegisterAndParseCustomDirective(): void
    {
        $this->registry->register(new DirectiveConfig(
            name: 'auth',
            hasArguments: false,
            allowedConnections: ['guest'],
            rendererClass: 'App\\Renderer\\AuthRenderer'
        ));

        $this->registry->register(new DirectiveConfig(
            name: 'guest',
            hasArguments: false,
            allowedConnections: [],
            rendererClass: 'App\\Renderer\\GuestRenderer'
        ));

        $template = '@auth { <div>Painel Logado</div> } @guest { <div>Login</div> }';
        $tokens = $this->lexer->tokenize($template);
        $ast = $this->parser->parse($tokens);

        $children = $ast->root->getChildren();
        $this->assertCount(1, $children);

        /** @var DirectiveNode $authNode */
        $authNode = $children[0];

        $this->assertInstanceOf(DirectiveNode::class, $authNode);
        $this->assertSame('auth', $authNode->name);
        $this->assertSame('App\\Renderer\\AuthRenderer', $authNode->rendererClass);

        $this->assertCount(1, $authNode->branches);
        $this->assertSame('guest', $authNode->branches[0]->name);
        $this->assertSame('App\\Renderer\\GuestRenderer', $authNode->branches[0]->rendererClass);
    }
}
