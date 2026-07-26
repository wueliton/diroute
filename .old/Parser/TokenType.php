<?php

namespace Example\Parser;

enum TokenType
{
    case TEXT;
    case EOF;
    case SCOPE_END;
    case DIRECTIVE_NAME;
    case SCOPE_START;
    case ARGUMENTS;
    case EXPRESSION;
    case COMPONENT_START;
    case COMPONENT_END;
    case ATTRIBUTES;
}
