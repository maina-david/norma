<?php

namespace App\Console\Commands\Once;

use App\Models\Workflows\TaskRoute;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class UpdateTaskRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:update-task-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the task routes to use new route names.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $routes = [
            'Legislation Document Edit Page' => [
                'route_name' => 'collaborate.document-editor.index',
                'parameters' => [
                    'task' => 'id',
                    'expression' => 'document.work_expression_id',
                ],
                'permission' => 'editDocument',
                'permission_parameters' => ['document.expression', null], // null returns the task
                'fallback' => 'collaborate.work-expressions.preview',
            ],
            'Annotations Page' => [
                'route_name' => 'collaborate.annotations.index',
                'parameters' => [
                    'task' => 'id',
                    'expression' => 'document.work_expression_id',
                ],
                'permission' => 'annotate',
                'permission_parameters' => ['document.expression', null],
                'fallback' => 'collaborate.work-expressions.preview',
            ],
            'Legal Update Edit Page' => [
                'route_name' => 'collaborate.legal-updates.edit',
                'parameters' => [
                    'task' => 'id',
                    'legal_update' => 'document.legal_update_id',
                ],
                'permission' => 'edit',
                'permission_parameters' => ['document.legalUpdate', null],
                'fallback' => 'collaborate.legal-updates.show',
            ],
        ];

        TaskRoute::all()->each(function ($route) use ($routes) {
            $route->update($routes[$route->name]);
        });

        $this->info('Done!');

        return 0;
    }
}
