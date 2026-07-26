<?php

namespace Diroute\Compiler\Lexer;

enum TokenType: string
{
    // Texto estático HTML/Texto plano
    case T_HTML = 'T_HTML';

        // Interpolação: {{ $var }}
    case T_INTERPOLATION = 'T_INTERPOLATION';

        // Diretivas estruturais: @if, @elseif, @else, @for, @empty
    case T_DIRECTIVE_NAME = 'T_DIRECTIVE_NAME'; // ex: "if", "for"
    case T_DIRECTIVE_ARG = 'T_DIRECTIVE_ARG';   // ex: "(users as user)" ou "(logicExpression)"
    case T_BLOCK_OPEN = 'T_BLOCK_OPEN';         // {
    case T_BLOCK_CLOSE = 'T_BLOCK_CLOSE';       // }
    case T_COMPONENT_OPEN = 'T_COMPONENT_OPEN';
    case T_COMPONENT_PROPS = 'T_COMPONENT_PROPS';
    case T_COMPONENT_CLOSE = 'T_COMPONENT_CLOSE';

        // Fim do Arquivo
    case T_EOF = 'T_EOF';
}
