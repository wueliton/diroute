<?php

use Diroute\Compiler\Lexer\Lexer;
use Diroute\Compiler\Lexer\TokenType;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    private Lexer $lexer;

    protected function setUp(): void
    {
        $this->lexer = new Lexer();
    }

    public function testTokenizeStaticHtml(): void
    {
        $source = '<h1>Olá Mundo</h1>';
        $tokens = $this->lexer->tokenize($source);

        $this->assertCount(2, $tokens); // T_HTML + T_EOF
        $this->assertSame(TokenType::T_HTML, $tokens[0]->type);
        $this->assertSame('<h1>Olá Mundo</h1>', $tokens[0]->value);
        $this->assertSame(TokenType::T_EOF, $tokens[1]->type);
    }

    public function testTokenizeInterpolation(): void
    {
        $source = '<div>{{ $user->name }}</div>';
        $tokens = $this->lexer->tokenize($source);

        $this->assertCount(4, $tokens);
        $this->assertSame(TokenType::T_HTML, $tokens[0]->type);
        $this->assertSame('<div>', $tokens[0]->value);

        $this->assertSame(TokenType::T_INTERPOLATION, $tokens[1]->type);
        $this->assertSame('$user->name', $tokens[1]->value);

        $this->assertSame(TokenType::T_HTML, $tokens[2]->type);
        $this->assertSame('</div>', $tokens[2]->value);
    }

    public function testTokenizeDirectiveWithArgumentsAndBlock(): void
    {
        $source = '@if($isActive) { <p>Ativo</p> }';
        $tokens = $this->lexer->tokenize($source);

        // Tokens esperados: T_DIRECTIVE_NAME, T_DIRECTIVE_ARG, T_BLOCK_OPEN, T_HTML, T_BLOCK_CLOSE, T_EOF
        $this->assertSame(TokenType::T_DIRECTIVE_NAME, $tokens[0]->type);
        $this->assertSame('if', $tokens[0]->value);

        $this->assertSame(TokenType::T_DIRECTIVE_ARG, $tokens[1]->type);
        $this->assertSame('$isActive', $tokens[1]->value);

        $this->assertSame(TokenType::T_BLOCK_OPEN, $tokens[2]->type);
        $this->assertSame('{', $tokens[2]->value);

        $this->assertSame(TokenType::T_HTML, $tokens[3]->type);
        $this->assertSame(' <p>Ativo</p> ', $tokens[3]->value);

        $this->assertSame(TokenType::T_BLOCK_CLOSE, $tokens[4]->type);
        $this->assertSame('}', $tokens[4]->value);
    }

    public function testTokenizeForAndEmptyBlock(): void
    {
        $template = '@for(users as user) { <span>{{ user.name }}</span> } @empty { <p>Vazio</p> }';
        $tokens = $this->lexer->tokenize($template);

        $directiveNames = array_values(array_filter(
            $tokens,
            fn($t) => $t->type === TokenType::T_DIRECTIVE_NAME
        ));

        $this->assertCount(2, $directiveNames);
        $this->assertSame('for', $directiveNames[0]->value);
        $this->assertSame('empty', $directiveNames[1]->value);
    }

    public function testHandlesLooseAtSymbolAsHtml(): void
    {
        $source = 'Fale conosco via email@domain.com ou @twitter';
        $tokens = $this->lexer->tokenize($source);

        // O 'twitter' será identificado como T_DIRECTIVE_NAME pelo Lexer,
        // mas o Parser converterá para T_HTML por não estar no Registry.
        $this->assertSame(TokenType::T_HTML, $tokens[0]->type);
        $this->assertSame('Fale conosco via email', $tokens[0]->value);

        $this->assertSame(TokenType::T_DIRECTIVE_NAME, $tokens[1]->type);
        $this->assertSame('domain', $tokens[1]->value);
    }
}
