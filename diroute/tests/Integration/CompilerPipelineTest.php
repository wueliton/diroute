<?php

use Diroute\Compiler\CompilerEngine;
use Diroute\Compiler\Parser\Registry\DirectiveRegistry;
use Diroute\Compiler\Runtime\TemplateRunner;
use PHPUnit\Framework\TestCase;

class CompilerPipelineTest extends TestCase
{
    private CompilerEngine $compiler;
    private TemplateRunner $runner;

    protected function setUp(): void
    {
        $registry = new DirectiveRegistry();
        $this->compiler = new CompilerEngine($registry);
        $this->runner = new TemplateRunner();
    }

    public function testCompilesAndRendersCompleteTemplateWithRuntimeData(): void
    {
        $template = <<<HTML
<div class="dashboard">
    <h1>Olá {{ \$user->name }}</h1>

    @if(\$user->isAdmin()) {
        <span class="badge">Administrador</span>
    } @elseif(\$user->isEditor()) {
        <span class="badge">Editor</span>
    } @else {
        <span class="badge">Membro</span>
    }

    <h2>Lista de Tarefas</h2>
    @for(\$tasks as \$task) {
        <p>Item: {{ \$task->title }}</p>
    } @empty {
        <p>Nenhuma tarefa pendente.</p>
    }
</div>
HTML;

        $compiledPhp = $this->compiler->compile($template);

        // Prepara objeto e dados para o teste
        $user = new class {
            public string $name = 'Paulo';
            public function isAdmin(): bool
            {
                return true;
            }
            public function isEditor(): bool
            {
                return false;
            }
        };

        $task1 = new stdClass();
        $task1->title = 'Criar Módulo HTTP';
        $task2 = new stdClass();
        $task2->title = 'Configurar Router Next-Style';
        $tasks = [$task1, $task2];

        $renderedHtml = $this->runner->render($compiledPhp, [
            'user' => $user,
            'tasks' => $tasks,
        ]);

        $this->assertStringContainsString('<h1>Olá Paulo</h1>', $renderedHtml);
        $this->assertStringContainsString('<span class="badge">Administrador</span>', $renderedHtml);
        $this->assertStringContainsString('<p>Item: Criar Módulo HTTP</p>', $renderedHtml);
        $this->assertStringContainsString('<p>Item: Configurar Router Next-Style</p>', $renderedHtml);
    }
}
