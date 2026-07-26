<?php

namespace Example\Compiler;

use Example\AST\AttributeNode;
use Example\AST\ComponentNode;
use Example\AST\ExpressionNode;
use Example\AST\GenericDirectiveNode;
use Example\AST\InterpolatedValueNode;
use Example\AST\TextNode;
use Example\Component\ComponentRegistry;
use Example\Directive\DirectiveRegistry;

class Compiler
{
    private array $rendererCache = [];

    public function compile(array $nodes): string
    {
        $compiled = [];

        foreach ($nodes as $node) {
            $compiled[] = $this->compileNode($node);
        }

        return implode('', $compiled);
    }

    private function compileNode(object $node): string
    {
        if ($node instanceof GenericDirectiveNode) {
            return $this->compileDirective($node);
        }

        if ($node instanceof TextNode) {
            return $this->compileText($node);
        }

        if ($node instanceof ExpressionNode) {
            return $this->compileExpression($node);
        }

        if ($node instanceof ComponentNode) {
            return $this->compileComponent($node);
        }

        if ($node instanceof AttributeNode) {
            return $this->compileAttributes($node);
        }

        return '';
    }

    public function compileDirective(GenericDirectiveNode $node): string
    {
        $directiveConfig = DirectiveRegistry::get($node->name);
        $content = '';
        $validDirectiveConfig = isset($directiveConfig) && $directiveConfig->rendererClass;

        if ($validDirectiveConfig) {
            $renderer = $this->rendererCache[$directiveConfig->name] ??= new $directiveConfig->rendererClass();
            $content .= $renderer->render($node, $this);
        }

        return $content;
    }

    private function compileText(TextNode $node)
    {
        return $node->text;
    }

    private function compileExpression(ExpressionNode $node)
    {
        return "<?= htmlspecialchars({$node->expression}, ENT_QUOTES, 'UTF-8'); ?>";
    }

    private function compileComponent(ComponentNode $node)
    {
        if (ComponentRegistry::isRegistered($node->name)) {
            $propsCodeArray = [];
            foreach ($node->attributes as $attrNode) {
                $name = $attrNode->name;
                $valueNode = $attrNode->value;

                if (str_starts_with($name, ':')) {
                    $propName = substr($name, 1);
                    $expr = $valueNode instanceof TextNode ? $valueNode->text : '';
                    if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $expr)) {
                        $expr = '$' . $expr;
                    }
                    $propsCodeArray[] = self::phpString($propName) . ' => ' . $expr;
                } elseif (str_starts_with($name, '[') && str_ends_with($name, ']')) {
                    $propName = substr($name, 1, -1);
                    $expr = $valueNode instanceof TextNode ? $valueNode->text : '';
                    if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $expr)) {
                        $expr = '$' . $expr;
                    }
                    $propsCodeArray[] = self::phpString($propName) . ' => ' . $expr;
                } else {
                    $propName = $name;
                    $phpExpr = $this->compileAttributeValueToPhpExpression($valueNode);
                    $propsCodeArray[] = self::phpString($propName) . ' => ' . $phpExpr;
                }
            }

            $propsString = '[' . implode(', ', $propsCodeArray) . ']';
            $compiledBody = $this->compile($node->body);

            return '<?= \Example\Runtime\ComponentRuntime::render('
                . self::phpString($node->name) . ', '
                . $propsString . ', '
                . 'function($__vars) { extract($__vars, EXTR_SKIP); ?>'
                . $compiledBody
                . '<?php }, '
                . 'get_defined_vars()'
                . '); ?>';
        }

        $attrs = $this->compile($node->attributes);

        return "<{$node->name}{$attrs}>{$this->compile($node->body)}</{$node->name}>";
    }

    private function compileAttributeValueToPhpExpression(mixed $value)
    {
        if ($value instanceof TextNode) {
            return self::phpString($value->text);
        }

        if ($value instanceof ExpressionNode) {
            return $value->expression;
        }

        if ($value instanceof InterpolatedValueNode) {
            $parts = [];
            foreach ($value->parts as $part) {
                if ($part instanceof TextNode) {
                    $parts[] = var_export($part->text, true);
                } elseif ($part instanceof ExpressionNode) {
                    $parts[] = '(' . $part->expression . ')';
                }
            }
            return implode(' . ', $parts);
        }

        return 'null';
    }

    private static function phpString(string $value): string
    {
        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
    }

    private function compileAttributes(AttributeNode $node)
    {
        $rawValue = $node->value;
        $value = "";

        if ($rawValue instanceof TextNode) {
            $value .= $this->compileText($rawValue);
        }

        if ($rawValue instanceof ExpressionNode) {
            $value .= $this->compileExpression($rawValue);
        }

        if ($rawValue instanceof InterpolatedValueNode) {
            $value .= $this->compile($rawValue->parts);
        }

        return " {$node->name}='{$value}'";
    }
}
